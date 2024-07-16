<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "oc_category_to_store")]

class CategoryStore
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'stores')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'category_id')]
    private Category $category;

    #[ORM\Id]
    #[ORM\Column(name: 'store_id', type: 'integer', nullable: false)]
    private int $storeId = 0;

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        $category->addStore($this);
        return $this;
    }

    public function getStoreId(): ?int
    {
        return $this->storeId;
    }

    public function setStoreId(?int $storeId): self
    {
        $this->storeId = $storeId;

        return $this;
    }
}
