<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="oc_option")
 */
class Option
{
    /**
     * @ORM\Id
     * @ORM\Column(name="option_id")
     */
    private int $optionId = 0;

    /**
     * @ORM\Column(name="type", length=32, nullable=false)
     */
    private string $type = '';

    /**
     * @ORM\Column(name="sort_order", nullable=false)
     */
    private int $sortOrder = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OptionValue", mappedBy="option")
     */
    private Collection $optionValues;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OptionValueDescription", mappedBy="option")
     */
    private Collection $optionValueDescriptions;

    public function __construct()
    {
        $this->optionValues = new ArrayCollection();
        $this->optionValueDescriptions = new ArrayCollection();
    }

    public function getOptionId(): int
    {
        return $this->optionId;
    }

    public function setOptionId(int $optionId): self
    {
        $this->optionId = $optionId;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getOptionValues(): Collection
    {
        return $this->optionValues;
    }

    public function setOptionValues(Collection $optionValues): self
    {
        $this->optionValues = $optionValues;

        return $this;
    }

    public function getOptionValueDescriptions(): Collection
    {
        return $this->optionValueDescriptions;
    }

    public function setOptionValueDescriptions(Collection $optionValueDescriptions): self
    {
        $this->optionValueDescriptions = $optionValueDescriptions;

        return $this;
    }
}
