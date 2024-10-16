<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="oc_category_description", indexes={
 *     @ORM\Index(name="name", columns={"name"})
 * })
 */
class CategoryDescription
{
    public const DEFAULT_LANGUAGE_ID = 1;

    /**
     * @ORM\Id
     * @ORM\Column(name="language_id", type="integer", nullable=false)
     */
    private int $languageId = self::DEFAULT_LANGUAGE_ID;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private string $name = '';

    /**
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private string $description = '';

    /**
     * @ORM\Column(name="meta_title", type="string", length=255, nullable=false)
     */
    private string $metaTitle = '';

    /**
     * @ORM\Column(name="meta_description", type="string", length=255, nullable=false)
     */
    private string $metaDescription = '';

    /**
     * @ORM\Column(name="meta_keyword", type="string", length=255, nullable=false)
     */
    private string $metaKeyword = '';

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="descriptions")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="category_id")
     */
    private Category $category;

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLanguageId(int $languageId): self
    {
        $this->languageId = $languageId;

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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getMetaTitle(): string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(string $metaTitle): self
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaKeyword(): string
    {
        return $this->metaKeyword;
    }

    public function setMetaKeyword(string $metaKeyword): self
    {
        $this->metaKeyword = $metaKeyword;

        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        $category->addDescription($this);

        return $this;
    }
}
