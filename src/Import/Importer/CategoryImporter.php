<?php

declare(strict_types=1);

namespace App\Import\Importer;

use App\Config\Config;
use App\DTO\CategoryDto;
use App\Entity\Category;
use App\Entity\CategoryDescription;
use App\Entity\CategoryPath;
use App\Entity\CategoryStore;
use App\Import\ImportValidator;
use App\Import\Reader\ReaderInterface;
use App\Stats;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CategoryImporter implements ImporterInterface
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
        $progressIndicator->start('Processing categories...');

        foreach ($this->reader->read() as $rows) {
            $this->stats->increaseRowsCountInFile(count($rows));

            $categories = $this->transform($rows);
            $this->validator->validate($categories, $this->stats);
            $this->saveCategoriesToDb($categories);

            $progressIndicator->advance();
        }

        if (Config::$deactivateEmptyCategories) {
            $this->deactivateEmptyCategories();
        }

        $this->stats->setUsedMemory((int)(memory_get_usage() / 1024 / 1024));
        $this->stats->setEndTime(new DateTimeImmutable('now'));

        $progressIndicator->finish('Finish processing categories.');
        $this->stats->setEndTime(new DateTimeImmutable('now'));

        $this->stats->saveLog();

        return $this->stats;
    }

    public function setSource(string $source): ImporterInterface
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
        return Category::class === $targetClass;
    }

    public function deactivateEmptyCategories(): void
    {
        // enable all
        $results = $this->em->getRepository(Category::class)->findAll();

        $this->em->beginTransaction();
        /** @var Category $result */
        foreach ($results as $category) {
            $category->setStatus(Category::CATEGORY_STATUS_ENABLED);
        }

        $this->em->flush();
        $this->em->commit();

        // disable category without products
        $rsm = new ResultSetMappingBuilder($this->em);
        $rsm->addRootEntityFromClassMetadata(Category::class, 'c');

        $qr = $this->em->createNativeQuery($this->getCategoriesWithoutProductsQuery(), $rsm);
        $results = $qr->getResult();

        $this->em->beginTransaction();
        /** @var Category $result */
        foreach ($results as $category) {
            $category->setStatus(Category::CATEGORY_STATUS_DISABLED);
        }

        $this->em->flush();
        $this->em->commit();
    }

    private function loadDescription(Category $category, CategoryDto $data): void
    {
        $desc = $category->getDescriptionByKey(CategoryDescription::DEFAULT_LANGUAGE_ID);

        if ($desc) {
            $desc->setName($data->getName())
                ->setMetaTitle($data->getName());
        } else {
            $desc = (new CategoryDescription())
                ->setCategory($category)
                ->setLanguageId(CategoryDescription::DEFAULT_LANGUAGE_ID)
                ->setName($data->getName())
                ->setMetaTitle($data->getName());

            $this->em->persist($desc);
        }
    }

    /**
     * Main action for category import
     *
     * logic:
     * we go through all categories from dataset and try to find category in existed categories
     * if category exist, we update it
     * if category not exist, we create new category
     * after that we try to find existed path (parent->child relations) for category if it not exist, we add it
     * and tie category with store
     */
    private function saveCategoriesToDb(array $dataSet): void
    {
        $existedCategories = $this->em->getRepository(Category::class)->getCategories();

        /** @var CategoryDto $data */
        $this->em->beginTransaction();
        $this->stats->incrementTransactionCount();
        foreach ($dataSet as $data) {
            /** @var Category $currentCategory */
            $currentCategory = $existedCategories[$data->getCategoryId()] ?? null;

            if ($currentCategory) {
                $currentCategory->setParentId($data->getParentId());
                $this->stats->incrementCountUpdated();
            } else {
                $currentCategory = (new Category())
                    ->setCategoryId($data->getCategoryId())
                    ->setParentId($data->getParentId());
                $existedCategories[$data->getCategoryId()] = $currentCategory;

                $this->stats->incrementCountCreated();
                $this->em->persist($currentCategory);
            }

            $this->loadDescription($currentCategory, $data);
            $this->tieWithPath($currentCategory);
            $this->tieWithStore($currentCategory);
        }

        $this->em->flush();
        $this->em->commit();
    }

    private function tieWithStore($currentCategory): void
    {
        $newStore = (new CategoryStore())
            ->setCategory($currentCategory);

        if (!$currentCategory->hasStore($newStore)) {
            $currentCategory->addStore($newStore);
            $this->em->persist($newStore);
        }
    }

    private function tieWithPath(Category $currentCategory): void
    {
        $hasZeroLevel = false;
        $hasFirstLevel = false;
        $newTies = [];

        /** @var CategoryPath $path */
        foreach ($currentCategory->getPaths() as $path) {
            if ($path->getLevel() == CategoryPath::ZERO_PATH_LEVEL) {
                $hasZeroLevel = true;
            }

            if ($path->getLevel() == CategoryPath::FIRST_PATH_LEVEL) {
                $hasFirstLevel = true;
            }
        }

        if (!$hasFirstLevel) {
            $newTies[] = (new CategoryPath())
                ->setPathId($currentCategory->getCategoryId())
                ->setCategory($currentCategory)
                ->setLevel(CategoryPath::FIRST_PATH_LEVEL);
        }

        if (!$hasZeroLevel) {
            $newTies[] = (new CategoryPath())
                ->setPathId($currentCategory->getParentId())
                ->setCategory($currentCategory)
                ->setLevel(CategoryPath::ZERO_PATH_LEVEL);
        }

        /** @var CategoryPath $tie */
        foreach ($newTies as $tie) {
            $currentCategory->addPath($tie);
        }
    }

    private function initSerializer(): void
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $normalizers = [
            new DateTimeNormalizer(),
            new ObjectNormalizer($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter()),
        ];
        $serializer = new Serializer($normalizers);

        $this->serializer = $serializer;
    }

    private function transform(array $rows): array
    {
        $products = [];
        foreach ($rows as $row) {
            $products[] = CategoryDto::fromArray($row);
        }

        return $products;
    }

    private function getCategoriesWithoutProductsQuery(): string
    {
        return
            <<<SQL
WITH RECURSIVE
    CategoryTree AS (
        SELECT c.category_id, c.parent_id FROM oc_category c
        UNION ALL
        SELECT c.category_id, c.parent_id FROM oc_category c INNER JOIN CategoryTree ct ON c.parent_id = ct.category_id
    ),
    CategoriesWithProducts AS (
       SELECT DISTINCT c.category_id FROM oc_product_to_category pc INNER JOIN oc_category c ON pc.category_id = c.category_id
    )
SELECT
    ct.category_id,
    ct.parent_id
FROM
    CategoryTree ct
LEFT JOIN
    CategoriesWithProducts cwp ON ct.category_id = cwp.category_id
WHERE
    cwp.category_id IS NULL;
SQL;
    }
}
