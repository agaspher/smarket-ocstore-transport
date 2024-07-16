<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Repository\ProductOptionValueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductOptionValueRepository::class)]
#[ORM\Table(name: "oc_product_option_value")]
class ProductOptionValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'product_option_value_id')]
    private ?int $productOptionValueId = null;

    #[ORM\ManyToOne(targetEntity: ProductOption::class, inversedBy: 'productOptionValues')]
    #[ORM\JoinColumn(name: 'product_option_id', referencedColumnName: 'product_option_id')]
    private ProductOption $productOption;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'options')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'product_id')]
    private Product $product;

    #[ORM\Column(name: 'product_id')]
    private int $productId;

    #[ORM\ManyToOne(targetEntity: Option::class, inversedBy: 'productOptions')]
    #[ORM\JoinColumn(name: 'option_id', referencedColumnName: 'option_id')]
    private Option $option;

    #[ORM\Column(name: 'option_id')]
    private int $optionId;

    #[ORM\ManyToOne(targetEntity: OptionValue::class, inversedBy: 'productOptionValues')]
    #[ORM\JoinColumn(name: 'option_value_id', referencedColumnName: 'option_value_id')]
    private OptionValue $optionValue;

    #[ORM\Column(name: 'quantity', type: 'integer', nullable: false)]
    private int $quantity = 1;

    #[ORM\Column(name: 'subtract', type: 'boolean', nullable: false)]
    private bool $subtract = true;

    #[ORM\Column(name: 'price', type: 'decimal', nullable: false)]
    private float $price = 0.0;

    #[ORM\Column(name: 'price_prefix', type: 'string', length: 1, nullable: false)]
    private string $pricePrefix = '+';

    #[ORM\Column(name: 'points', type: 'integer', nullable: false)]
    private int $points = 0;

    #[ORM\Column(name: 'points_prefix', type: 'string', length: 1, nullable: false)]
    private string $pointsPrefix = '+';

    #[ORM\Column(name: 'weight', type: 'decimal')]
    private float $weight = 0.0;

    #[ORM\Column(name: 'weight_prefix', type: 'string', length: 1, nullable: false)]
    private string $weightPrefix = '+';

    public function getProductOptionValueId(): ?int
    {
        return $this->productOptionValueId;
    }

    public function getProductOption(): ProductOption
    {
        return $this->productOption;
    }

    public function setProductOption(ProductOption $productOption): self
    {
        $this->productOption = $productOption;

        return $this;
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

    public function getOptionValue(): OptionValue
    {
        return $this->optionValue;
    }

    public function setOptionValue(OptionValue $optionValue): self
    {
        $this->optionValue = $optionValue;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function isSubtract(): bool
    {
        return $this->subtract;
    }

    public function setSubtract(bool $subtract): self
    {
        $this->subtract = $subtract;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPricePrefix(): string
    {
        return $this->pricePrefix;
    }

    public function setPricePrefix(string $pricePrefix): self
    {
        $this->pricePrefix = $pricePrefix;

        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;

        return $this;
    }

    public function getPointsPrefix(): string
    {
        return $this->pointsPrefix;
    }

    public function setPointsPrefix(string $pointsPrefix): self
    {
        $this->pointsPrefix = $pointsPrefix;

        return $this;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getWeightPrefix(): string
    {
        return $this->weightPrefix;
    }

    public function setWeightPrefix(string $weightPrefix): self
    {
        $this->weightPrefix = $weightPrefix;

        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(?int $productId): self
    {
        $this->productId = $productId;

        return $this;
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
}
