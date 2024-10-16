<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CategoryDto
{
    /** @Assert\NotNull(message="Category without categoryId") */
    private ?int $categoryId = null;
    /** @Assert\NotNull(message="Category without parentId") */
    private ?int $parentId = null;
    /** @Assert\Length(min=3, minMessage="Category with very short name") */
    private string $name = '';

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function setCategoryId(?int $categoryId): self
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEntityId(): int
    {
        return (int)$this->categoryId;
    }

    public static function fromArray(array $dataSet): self
    {
        return (new self())
            ->setCategoryId($dataSet['category_id'] ?? null)
            ->setParentId($dataSet['parent_id'] ?? null)
            ->setName($dataSet['name'] ?? '');
    }
}
