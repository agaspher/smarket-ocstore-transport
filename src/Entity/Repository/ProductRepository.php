<?php

declare(strict_types=1);

namespace App\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository
{
    public function getProductsBySku(array $skus): array
    {
        $products = $this->findBy(['sku' => array_unique($skus)]);

        $mapping = [];
        foreach ($products as $product) {
            $mapping[$product->getModel()] = $product;
        }

        return $mapping;
    }
}
