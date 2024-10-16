<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="oc_option_value")
 */
class OptionValue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="option_value_id")
     */
    private ?int $optionValueId = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Option", inversedBy="optionValues")
     * @ORM\JoinColumn(name="option_id", referencedColumnName="option_id")
     */
    private ?Option $option = null;

    /**
     * @ORM\Column(name="image", type="string", length=255, nullable=false)
     */
    private string $image = '';

    /**
     * @ORM\Column(name="sort_order", nullable=false)
     */
    private int $sortOrder = 0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OptionValueDescription", mappedBy="optionValue", cascade={"persist"})
     */
    private Collection $descriptions;

    public function __construct()
    {
        $this->descriptions = new ArrayCollection();
    }

    public function getOptionValueId(): ?int
    {
        return $this->optionValueId;
    }

    public function setOptionValueId(int $optionValueId): self
    {
        $this->optionValueId = $optionValueId;

        return $this;
    }

    public function getOption(): ?Option
    {
        return $this->option;
    }

    public function setOption(Option $option): self
    {
        $this->option = $option;

        return $this;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

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

    public function getDescriptions(): Collection
    {
        return $this->descriptions;
    }

    public function setDescriptions(Collection $descriptions): self
    {
        $this->descriptions = $descriptions;

        return $this;
    }

    public function addDescription(OptionValueDescription $desc): self
    {
        if (!$this->descriptions->contains($desc)) {
            $this->descriptions->add($desc);
            $desc->setOptionValue($this);
        }

        return $this;
    }
}
