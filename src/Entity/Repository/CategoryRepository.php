<?php

declare(strict_types=1);

namespace App\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function getCategories(): array
    {
        $categories = $this->findAll();

        $mapping = [];
        foreach ($categories as $category) {
            $mapping[$category->getCategoryId()] = $category;
        }

        return $mapping;
    }
}
