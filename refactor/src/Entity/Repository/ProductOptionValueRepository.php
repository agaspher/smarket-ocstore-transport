<?php

declare(strict_types=1);

namespace App\Entity\Repository;

use App\Config\Config;
use App\Entity\ProductOptionValue;
use Doctrine\ORM\EntityRepository;

class ProductOptionValueRepository extends EntityRepository
{
    public function getProductOptionValuesByProductIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $qb = $this->createQueryBuilder('pov');

        $qb->add(
            'where',
            $qb->expr()->andX(
                $qb->expr()->in('pov.productId', array_unique($ids)),
                $qb->expr()->eq('pov.optionId', Config::DEFAULT_OPTION_ID)
            )
        );
        $values = $qb->getQuery()->getResult();

        $mapping = [];
        /** @var ProductOptionValue $value */
        foreach ($values as $value) {
            $mapping[$value->getProductOptionValueId()] = $value;
        }

        return $mapping;
    }
}
