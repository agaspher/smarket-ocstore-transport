<?php

declare(strict_types=1);

namespace App\Import\Importer;

use App\Config\Config;
use App\DTO\SizeDto;
use App\Entity\Option;
use App\Entity\OptionDescription;
use App\Entity\OptionValue;
use App\Entity\OptionValueDescription;
use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\ProductOptionValue;
use App\Import\ImportValidator;
use App\Import\Reader\ReaderInterface;
use App\Stats;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Style\SymfonyStyle;

class SizesImporter implements ImporterInterface
{
    private EntityManager $em;
    private ImportValidator $validator;
    private ReaderInterface $reader;
    private Stats $stats;
    private string $source = '';

    public function import(SymfonyStyle $io): Stats
    {
        $this->stats->setStartTime(new DateTimeImmutable('now'));

        $progressIndicator = new ProgressIndicator($io);
        $progressIndicator->start('Processing sizes...');

        foreach ($this->reader->read() as $rows) {
            $this->stats->increaseRowsCountInFile(count($rows));

            $sizes = $this->transform($rows);
            $this->validator->validate($sizes, $this->stats);
            $this->saveSizesToDb($sizes);

            $progressIndicator->advance();
        }

        $this->stats->setUsedMemory((int)(memory_get_usage() / 1024 / 1024));
        $this->stats->setEndTime(new DateTimeImmutable('now'));

        $progressIndicator->finish('Finish processing sizes.');

        $this->stats->saveLog();
        return $this->stats;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function setEntityManager(EntityManager $entityManager): self
    {
        $this->em = $entityManager;

        return $this;
    }

    public function setValidator(ImportValidator $validator): self
    {
        $this->validator = $validator;

        return $this;
    }

    public function setReader(ReaderInterface $reader): self
    {
        $this->reader = $reader;

        return $this;
    }

    public function setStats(Stats $stats): ImporterInterface
    {
        $this->stats = $stats;

        return $this;
    }

    public static function matches(string $targetClass): bool
    {
        return Option::class == $targetClass;
    }

    private function transform(array $rows): array
    {
        $sizes = [];
        foreach ($rows as $row) {
            $sizes[] = SizeDto::fromArray($row);
        }

        return $sizes;
    }

    private function saveSizesToDb(array $sizes): void
    {
        if (!$this->isSizeOptionExists()) {
            throw new Exception(
                sprintf('Option for size with default id=[%s] doesn\'t exist!', Config::$defaultOptionId)
            );
        }

        $this->createOptionValues($sizes);
        $this->tieSizeWithProduct($sizes);
    }

    /**
     * Option - properties for products (in our case "size", id=13)
     * OptionDescription - name of property in different languages (in our case "Размер" for language_id=1)
     * OptionValue - size (logical value, lately here we can relate with names from OptionValueDescription)
     * OptionValueDescription - value (name, shown to user) of property (in our case size like a string, ex: XL, M, L)
     *
     * Creates new option values (the sizes for given size names if they don't exist in DB.
     * Creates new descriptions for them in default language.
     */
    private function createOptionValues(array $sizes): void
    {
        $names = array_map(fn(SizeDto $size) => $size->getRus(), $sizes);

        $defaultOption = $this->em->getRepository(Option::class)
            ->findOneBy(['optionId' => Config::$defaultOptionId]);
        $sizeOptions = $this->em->getRepository(OptionValueDescription::class)->getOptionValueByName($names);

        $this->em->beginTransaction();
        $this->stats->incrementTransactionCount();

        foreach ($names as $name) {
            if (!($sizeOptions[$name] ?? null)) {
                $newOptionValueDesc = (new OptionValueDescription())
                    ->setName($name)
                    ->setLanguageId(Config::$defaultLanguageId)
                    ->setOption($defaultOption);

                $newOptionValue = (new OptionValue())
                    ->addDescription($newOptionValueDesc)
                    ->setOption($defaultOption);

                $this->em->persist($newOptionValue);
                $this->em->persist($newOptionValueDesc);

                $this->stats->incrementCountCreated();
                $sizeOptions[$name] = $newOptionValueDesc;
            }
        }

        $this->em->flush();
        $this->em->commit();
    }

    private function isSizeOptionExists(): bool
    {
        // in oc_option already has to be option_id = default
        // and description for it in oc_option_description for default language
        // just check and if not throw Exception

        $sizeOption = $this->em
            ->getRepository(Option::class)
            ->findOneBy(['optionId' => Config::$defaultOptionId]);
        $sizeOptionDesc = $this->em->getRepository(OptionDescription::class)
            ->findOneBy(['optionId' => Config::$defaultOptionId, 'languageId' => Config::$defaultLanguageId]);

        return $sizeOption && $sizeOptionDesc;
    }

    private function tieSizeWithProduct(array $sizes): void
    {
        /** @var SizeDto $item */
        $skus = array_unique(array_map(fn($item) => $item->getArticul(), $sizes));
        $names = array_unique(array_map(fn($item) => $item->getRus(), $sizes));

        $products = $this->em->getRepository(Product::class)->getProductsBySku($skus);

        /** @var Product $product */
        $productIds = array_unique(array_map(fn($product) => $product->getProductId(), $products));

        $optionValueDescs = $this->em->getRepository(OptionValueDescription::class)
            ->getOptionValueByName($names);

        $ties = $this->em->getRepository(ProductOptionValue::class)->getProductOptionValuesByProductIds($productIds);

        $mapped = [];
        /** @var ProductOptionValue $tie */
        foreach ($ties as $tie) {
            $optionValue = $tie->getOptionValue()
                ->getDescriptions()
                ->filter(fn($desc) => $desc->getLanguageId() === Config::$defaultLanguageId)
                ->first();

            $name = null;
            if ($optionValue) {
                $name = $optionValue->getName();
            }

            if ($name) {
                $mapped[sprintf('%s-%s', $tie->getProduct()->getSku(), $name)] = $tie;
            }
        }

        $this->em->beginTransaction();
        /** @var SizeDto $size */
        foreach ($sizes as $size) {
            $product = $products[$size->getArticul()] ?? null;
            /** @var OptionValueDescription $optionValueDesc */
            $optionValueDesc = $optionValueDescs[$size->getRus()] ?? null;
            $hash = sprintf('%s-%s', $size->getArticul(), $size->getRus());

            if (!($product && $optionValueDesc) || ($mapped[$hash] ?? null)) {
                continue;
            }

            $newProductOption = (new ProductOption())
                ->setProduct($product)
                ->setOption($optionValueDesc->getOption());

            $newProductOptionValue = (new ProductOptionValue())
                ->setProductOption($newProductOption)
                ->setProduct($product)
                ->setOption($optionValueDesc->getOption())
                ->setOptionValue($optionValueDesc->getOptionValue());

            $this->em->persist($newProductOption);
            $this->em->persist($newProductOptionValue);
        }

        $this->em->flush();
        $this->em->commit();
    }
}
