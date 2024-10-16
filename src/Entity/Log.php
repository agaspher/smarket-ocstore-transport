<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="log")
 * @ORM\HasLifecycleCallbacks
 */
class Log
{
    public const IMPORT_TYPE_PRODUCT = 'product';
    public const IMPORT_TYPE_CATEGORY = 'category';
    public const IMPORT_TYPE_SIZE = 'size';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="import_type", type="string", length=255, nullable=false)
     */
    private string $importType = '';

    /**
     * @ORM\Column(name="entity_id", type="integer", nullable=false)
     */
    private int $entityId;

    /**
     * @ORM\Column(name="msg", type="text", nullable=false)
     */
    private string $msg = '';

    /**
     * @ORM\Column(name="date_added", type="datetime_immutable", nullable=false)
     */
    private DateTimeImmutable $dateAdded;

    /**
     * @ORM\Column(name="date_modified", type="datetime_immutable", nullable=false)
     */
    private DateTimeImmutable $dateModified;

    public function __construct()
    {
        $this->dateAdded = new DateTimeImmutable();
        $this->dateModified = new DateTimeImmutable();
    }

    /**
     * @ORM\PreUpdate
     */
    public function renewDateModified(): void
    {
        $this->dateModified = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMsg(): string
    {
        return $this->msg;
    }

    public function getImportType(): string
    {
        return $this->importType;
    }

    public function setImportType(string $importType): self
    {
        $this->importType = $importType;

        return $this;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function setMsg(string $msg): self
    {
        $this->msg = $msg;

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
}
