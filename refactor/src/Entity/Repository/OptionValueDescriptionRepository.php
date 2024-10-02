<?php

declare(strict_types=1);

namespace App\Entity\Repository;

use App\Config\Config;
use App\Entity\OptionValue;
use Doctrine\ORM\EntityRepository;

class OptionValueDescriptionRepository extends EntityRepository
{
    public function getOptionValueByName(array $names): array
    {
        $qb = $this->createQueryBuilder('ovd');

        $qb->add(
            'where',
            $qb->expr()->andX(
                $qb->expr()->in('ovd.name', array_unique($names)),
                $qb->expr()->eq('ovd.languageId', Config::$defaultLanguageId)
            )
        );

        $optionDescriptions = $qb->getQuery()->getResult();

        $mapping = [];
        /** @var OptionValue $value */
        foreach ($optionDescriptions as $desc) {
            $mapping[$desc->getName()] = $desc;
        }

        return $mapping;
    }
}
