<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="oc_product_option")
 */
class ProductOption
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="product_option_id")
     */
    private ?int $productOptionId = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", fetch="EAGER", inversedBy="productOptions")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id")
     */
    private Product $product;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Option", inversedBy="productOptions")
     * @ORM\JoinColumn(name="option_id", referencedColumnName="option_id")
     */
    private Option $option;

    /**
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    private string $value = '';

    /**
     * @ORM\Column(name="required", type="boolean")
     */
    private bool $required = false;

    public function getProductOptionId(): ?int
    {
        return $this->productOptionId;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getOption(): Option
    {
        return $this->option;
    }

    public function setOption(Option $option): self
    {
        $this->option = $option;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }
}
