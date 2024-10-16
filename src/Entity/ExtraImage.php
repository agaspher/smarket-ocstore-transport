<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="oc_product_image", indexes={@ORM\Index(name="product_id", columns={"product_id"})})
 */
class ExtraImage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="product_image_id", type="integer")
     */
    private ?int $productImageId = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="extraImages")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id")
     */
    private Product $product;

    /**
     * @ORM\Column(name="image", type="string", length=255, nullable=false)
     */
    private string $image = '';

    public function getProductImageId(): ?int
    {
        return $this->productImageId;
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

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
