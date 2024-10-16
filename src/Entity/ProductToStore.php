<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="oc_product_to_store")
 */
class ProductToStore
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="stores")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id")
     */
    private Product $product;

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}
