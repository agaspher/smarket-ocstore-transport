<?php

declare(strict_types=1);

namespace App\Import;

use App\Import\Importer\CategoryImporter;
use App\Import\Importer\ImporterInterface;
use App\Import\Importer\ProductImporter;
use App\Import\Importer\SizesImporter;
use App\Import\Reader\JsonReader;
use App\Import\Reader\ReaderInterface;
use App\Stats;
use Doctrine\ORM\EntityManager;
use Exception;

class ImporterFactory
{
    public const array AVAILABLE_READERS = [
        JsonReader::class
    ];

    public const array AVAILABLE_IMPORTERS = [
        ProductImporter::class,
        CategoryImporter::class,
        SizesImporter::class,
    ];

    public function createImporter(string $path, string $targetClass, EntityManager $em): ImporterInterface
    {
        $importer = null;
        foreach (self::AVAILABLE_IMPORTERS as $available) {
            if ($available::matches($targetClass)) {
                $importer = new $available();
                $importer->setSource($path)
                    ->setValidator(new ImportValidator())
                    ->setReader($this->getReader($path))
                    ->setStats(new Stats($em));
            }
        }

        if (!$importer) {
            throw new Exception(sprintf("Importer not found for class [%s]", $targetClass));
        }

        $importer->setReader($this->getReader($path));
        $importer->setEntityManager($em);

        return $importer;
    }

    private function getReader(?string $path = null): ReaderInterface
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        foreach (self::AVAILABLE_READERS as $reader) {
            if ($reader::matches($extension)) {
                return new $reader($path);
            }
        }

        throw new Exception(sprintf("Reader not found for extension [%s]", $extension));
    }
}
