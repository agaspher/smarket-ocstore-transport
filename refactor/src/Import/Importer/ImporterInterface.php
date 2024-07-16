<?php

declare(strict_types=1);

namespace App\Import\Importer;

use App\Import\ImportValidator;
use App\Stats;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Style\SymfonyStyle;

interface ImporterInterface
{
    public function import(SymfonyStyle $io): Stats;

    public function setSource(string $source): self;

    public function setEntityManager(EntityManager $entityManager): self;

    public function setValidator(ImportValidator $validator): self;

    public function setStats(Stats $stats): self;

    public static function matches(string $targetClass): bool;
}
