<?php
declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Repository\CategoryRepository")
 * @ORM\Table(name="oc_category")
 * @ORM\HasLifecycleCallbacks
 */
class Category
{
    public const CATEGORY_STATUS_DISABLED = 0;
    public const CATEGORY_STATUS_ENABLED = 1;

    public const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @ORM\Id
     * @ORM\Column(name="category_id", type="integer")
     */
    private int $categoryId = 0;

    /**
     * @ORM\Column(name="parent_id", type="integer", nullable=false)
     */
    private int $parentId = 0;

    /**
     * @ORM\Column(name="top", type="smallint", nullable=false)
     */
    private int $top = 1;

    /**
     * @ORM\Column(name="`column`", type="integer", nullable=false)
     */
    private int $column = 1;

    /**
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    private int $status = 1;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CategoryDescription", mappedBy="category", cascade={"persist"})
     */
    private Collection $descriptions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CategoryPath", mappedBy="category", cascade={"persist"})
     */
    private Collection $paths;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CategoryStore", mappedBy="category", cascade={"persist"})
     */
    private Collection $stores;

    /**
     * @ORM\Column(name="date_added", type="datetime_immutable", nullable=false)
     * @Groups({"sm_import"})
     * @Context(
     *     context={DateTimeNormalizer::FORMAT_KEY=self::MYSQL_DATETIME_FORMAT},
     *     groups={"sm_import"}
     * )
     */
    private DateTimeImmutable $dateAdded;

    /**
     * @ORM\Column(name="date_modified", type="datetime_immutable", nullable=false)
     * @Groups({"sm_import"})
     * @Context(
     *     context={DateTimeNormalizer::FORMAT_KEY=self::MYSQL_DATETIME_FORMAT},
     *     groups={"sm_import"}
     * )
     */
    private DateTimeImmutable $dateModified;

    /**
     * @ORM\Column(name="viewed_mmlivesearch", type="integer", nullable=false)
     */
    private int $viewedMmlivesearch = 1;

    public function __construct()
    {
        $this->dateAdded = new DateTimeImmutable();
        $this->dateModified = new DateTimeImmutable();
        $this->descriptions = new ArrayCollection();
        $this->stores = new ArrayCollection();
        $this->paths = new ArrayCollection();
    }

    /**
     * @ORM\PreUpdate
     */
    public function renewDateModified(): void
    {
        $this->dateModified = new DateTimeImmutable();
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): self
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    public function getDateAdded(): DateTimeImmutable
    {
        return $this->dateAdded;
    }

    public function setDateAdded(DateTimeImmutable $dateAdded): self
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    public function getDateModified(): DateTimeImmutable
    {
        return $this->dateModified;
    }

    public function setDateModified(DateTimeImmutable $dateModified): self
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): self
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDescriptions(): Collection
    {
        return $this->descriptions;
    }

    public function setDescriptions(Collection $descriptions): self
    {
        $this->descriptions = $descriptions;

        return $this;
    }

    public function hasDescription(CategoryDescription $check): bool
    {
        $found = $this->descriptions->filter(
            fn($desc) => (
                    $this->categoryId === $check->getCategory()->getCategoryId())
                && ($desc->getLanguageId() === $check->getLanguageId()
                )
        );

        if ($found->count() > 0) {
            return true;
        }

        return false;
    }

    public function getDescriptionByKey(int $languageId): ?CategoryDescription
    {
        $found = $this->descriptions->filter(
            fn($desc) => $desc->getLanguageId() === $languageId
        );

        if ($found->count() > 0) {
            return $found->first();
        }

        return null;
    }

    public function addDescription(CategoryDescription $desc): void
    {
        if (!$this->hasDescription($desc)) {
            $this->descriptions[] = $desc;
        }
    }

    public function getPaths(): Collection
    {
        return $this->paths;
    }

    public function addPath(CategoryPath $path): void
    {
        if (!$this->hasPath($path)) {
            $this->paths[] = $path;
        }
    }

    public function hasPath(CategoryPath $check): bool
    {
        $found = $this->paths->filter(
            fn($path) => (
                    $this->getCategoryId() === $check->getCategory()->getCategoryId())
                && $path->getPathId() === $check->getPathId()
        );

        if ($found->count() > 0) {
            return true;
        }

        return false;
    }

    public function hasStore(CategoryStore $check): bool
    {
        // or can make just return $this->stores->count() > 0;
        $found = $this->stores->filter(
            fn($store) => (
                $this->getCategoryId() === $check->getCategory()->getCategoryId())
        );

        if ($found->count() > 0) {
            return true;
        }

        return false;
    }

    public function addStore(CategoryStore $store): void
    {
        if (!$this->hasStore($store)) {
            $this->stores[] = $store;
        }
    }
}
