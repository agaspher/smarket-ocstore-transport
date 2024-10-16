<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="oc_category_path")
 */
class CategoryPath
{
    public const ZERO_PATH_LEVEL = 0;
    public const FIRST_PATH_LEVEL = 1;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="paths")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="category_id")
     */
    private Category $category;

    /**
     * @ORM\Id
     * @ORM\Column(name="path_id", type="integer", nullable=false)
     */
    private ?int $pathId = null;

    /**
     * @ORM\Column(name="level", type="integer", nullable=false)
     */
    private int $level = 1;

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        $category->addPath($this);
        return $this;
    }

    public function getPathId(): ?int
    {
        return $this->pathId;
    }

    public function setPathId(?int $pathId): self
    {
        $this->pathId = $pathId;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }
}
