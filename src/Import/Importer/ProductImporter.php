<?php

declare(strict_types=1);

namespace App\Import\Importer;

use App\Config\Config;
use App\DTO\ProductDto;
use App\Entity\Category;
use App\Entity\ExtraImage;
use App\Entity\Product;
use App\Entity\ProductDescription;
use App\Entity\ProductToStore;
use App\Import\ImportValidator;
use App\Import\Reader\ReaderInterface;
use App\Stats;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ProductImporter implements ImporterInterface
{
    private EntityManager $em;
    private ImportValidator $validator;
    private ReaderInterface $reader;
    private Stats $stats;

    private ?Serializer $serializer = null;

    public function import(SymfonyStyle $io): Stats
    {
        $this->initSerializer();
        $progressIndicator = new ProgressIndicator($io);

        $this->stats->setStartTime(new DateTimeImmutable('now'));
        $progressIndicator->start('Processing products...');

        foreach ($this->reader->read() as $rows) {
            $this->stats->increaseRowsCountInFile(count($rows));
            $products = $this->transform($rows);
            $this->validator->validate($products, $this->stats);
            $this->saveProductsToDb($products);

            $progressIndicator->advance();
        }

        $this->stats->setUsedMemory((int)(memory_get_usage() / 1024 / 1024));
        $this->stats->setEndTime(new DateTimeImmutable('now'));
        $progressIndicator->finish('Finish processing products.');

        $this->stats->saveLog();
        return $this->stats;
    }

    /**
     * Create categories from products if they don't exist. This categories will be used when we saving products to database.
     * Categories will be saved to database in the same transaction as products.
     */
    private function createCategoriesFromProducts(array $dataSet): void
    {
        $existedCategories = $this->em->getRepository(Category::class)->getCategories();

        $this->em->beginTransaction();
        foreach ($dataSet as $data) {
            if (!array_key_exists($data->getClassif(), $existedCategories)) {
                $newCategory = (new Category)->setCategoryId($data->getClassif());
                $existedCategories[$data->getClassif()] = $newCategory;

                $this->em->persist($newCategory);
            }
        }

        $this->em->flush();
        $this->em->commit();
    }

    private function loadDescription(Product $product, ProductDto $data): void
    {
        $desc = $product->getDescriptionByKey(ProductDescription::DEFAULT_LANGUAGE_ID);

        if ($desc) {
            $desc->setName($data->getName())
                ->setDescription($data->getInfo())
                ->setMetaTitle($data->getName());
        } else {
            $desc = (new ProductDescription())
                ->setProduct($product)
                ->setLanguageId(ProductDescription::DEFAULT_LANGUAGE_ID)
                ->setName($data->getName())
                ->setDescription($data->getInfo())
                ->setMetaTitle($data->getName());

            $this->em->persist($desc);
        }
    }

    /**
     * If extra image (the regular image is stored in product table) already exist, it will be updated.
     * If extra image not exist, it will be created.
     */
    private function loadExtraImages(Product $product, ProductDto $data): void
    {
        if ($data->photoCount() > 1) {
            foreach ($data->getExtraImages() as $image) {
                $processingImage = Config::$ocsImgPath . '/' . $image;

                $dbExtraImage = $product->getExtraImage($processingImage);

                if ($dbExtraImage) {
                    $dbExtraImage->setImage($processingImage);
                } else {
                    $dbExtraImage = (new ExtraImage())
                        ->setProduct($product)
                        ->setImage($processingImage);

                    $this->em->persist($dbExtraImage);
                }
            }
        }
    }

    private function initSerializer(): void
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader());
        $normalizers = [
            new DateTimeNormalizer(),
            new ObjectNormalizer($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter()),
        ];
        $serializer = new Serializer($normalizers);

        $this->serializer = $serializer;
    }

    private function insertOnDuplicateKey(array $products): void
    {
        $fields = array_keys($this->serializer->normalize($products[0], null, ['groups' => 'sm_import']));

        $sql = "INSERT INTO `oc_product` (" . join(',', $fields) . ") VALUES ";

        $values = [];
        foreach ($products as $product) {
            $values[] = $this->serializer->normalize($product, null, ['groups' => 'sm_import']);
        }

        $questionMarks = array_fill(0, count($values[0]), '?');
        $lines = array_fill(0, count($values), '(' . join(',', $questionMarks) . ')');

        $questionMarks = join(', ', $lines);

        $sql .= $questionMarks . ' ON DUPLICATE KEY UPDATE ';
        $sql .= '`sku`=`sku`';

        $inlineValues = call_user_func_array('array_merge', array_map('array_values', $values));

        $rsm = new ResultSetMapping();
        $query = $this->em->createNativeQuery($sql, $rsm);

        $query->setParameters($inlineValues);
        $query->execute();
    }

    private function updateProduct(Product $product, ProductDto $data): Product
    {
        $this->stats->incrementCountUpdated();

        $product
            ->setModel($data->getArticul())
            ->setImage(Config::$ocsImgPath . '/' . $data->getFirstImage())
            ->setPrice($data->getPrice())
            ->setMpn($data->getMpn());

        return $product;
    }

    private function createProduct(ProductDto $product): Product
    {
        $this->stats->incrementCountCreated();

        return (new Product())
            ->setModel($product->getArticul())
            ->setSku($product->getArticul())
            ->setImage(Config::$ocsImgPath . '/' . $product->getFirstImage())
            ->setPrice($product->getPrice())
            ->setMpn($product->getMpn());
    }

    /**
     * Save products to database. It creates categories from products if they not exist.
     * If product exist in database, it will be updated, otherwise new product will be created
     */
    private function saveProductsToDb(array $dataSet): void
    {
        $this->createCategoriesFromProducts($dataSet);

        $skus = [];
        /** @var ProductDto $product */
        foreach ($dataSet as $product) {
            $skus[] = $product->getArticul();
        }

        $products = $this->em->getRepository(Product::class)->getProductsBySku($skus);

        $this->em->beginTransaction();
        $this->stats->incrementTransactionCount();

        foreach ($dataSet as $data) {
            $currentProduct = $products[$data->getArticul()] ?? null;

            if ($currentProduct) {
                $this->updateProduct($currentProduct, $data);
            } else {
                $currentProduct = $this->createProduct($data);

                $this->em->persist($currentProduct);
            }

            $this->loadExtraImages($currentProduct, $data);
            $this->loadDescription($currentProduct, $data);
            $this->tieWithCategory($currentProduct, $data);
            $this->tieWithStore($currentProduct);
        }

        $this->em->flush();
        $this->em->commit();
        $this->em->clear();

        // we can make it over insertOnDuplicate but its terribly slow
        // so still here only for experiment but it works and can be used
//        $newProducts = [];
//
//        /** @var ProductDto $product */
//        foreach ($products as $product) {
//            $newProducts[] = (new Product())
//                ->setModel($product->getArticul())
//                ->setSku($product->getArticul())
//                ->setImage($product->getFirstImage())
//                ->setPrice($product->getPrice())
//                ->setMpn($product->getMpn());
//        }
//
//        $this->insertOnDuplicateKey($newProducts);
    }

    /**
     * Ties product with category
     * If category not exist, we create it
     * If product already has category, we remove it from this category and tie with the new one
     */
    private function tieWithCategory(Product $product, ProductDto $data): void
    {
        $categories = $this->em->getRepository(Category::class)->getCategories();

        $currentCategory = $categories[$data->getClassif()] ?? null;
        $productCategory = $product->getCategories()->first();

        if ($currentCategory) {
            $newCat = $currentCategory;
        } else {
            $newCat = (new Category())->setCategoryId($data->getClassif());
            $this->em->persist($newCat);
        }

        if ($productCategory) {
            $product->getCategories()->removeElement($productCategory);
        }

        $product->addCategory($newCat);
    }

    /**
     * Ties product with store
     * If product already has store, we do nothing
     */
    private function tieWithStore(Product $product): void
    {
        $store = $product->getStore();

        if (!$store) {
            $newStore = (new ProductToStore())
                ->setProduct($product);

            $product->addStore($newStore);
            $this->em->persist($newStore);
        }
    }

    public function setSource(string $source): self
    {
        //doesn't need for product importer
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
        return Product::class === $targetClass;
    }

    private function transform(array $rows): array
    {
        $products = [];
        foreach ($rows as $row) {
            $products[] = ProductDto::fromArray($row);
        }

        return $products;
    }
}
