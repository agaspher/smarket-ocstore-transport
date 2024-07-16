<?php

declare(strict_types=1);

namespace App\Import;

use App\DTO\ProductDto;
use App\Entity\Category;
use App\Entity\ExtraImage;
use App\Entity\Product;
use App\Entity\ProductDescription;
use App\Entity\ProductToStore;
use App\Import\Reader\JsonReader;
use App\Import\Reader\ReaderInterface;
use App\Stats;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Exception;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;

class Importer
{

    private EntityManager $em;

    private Stats $stats;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->stats = new Stats();
    }

    public function getStats(): Stats
    {
        return $this->stats;
    }
}
