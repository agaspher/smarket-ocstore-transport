<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ProductDto
{
    /** @Assert\Length(min=3, minMessage="Product with very short articul") */
    private ?string $articul = '';

    /** @Assert\NotNull(message="Product without category") */
    private ?int $classif = null;

    private ?int $mesuriment = null;

    /** @Assert\Length(min=10, minMessage="Product with very short title") */
    private string $name = '';

    /** @Assert\Length(min=5, minMessage="Product without description") */
    private string $info = '';

    /** @Assert\GreaterThan(value=0, message="Product without price") */
    private float $price = 0.0;

    /** @Assert\NotBlank(message="Product without country") */
    private string $country = '';

    /** @Assert\Count(min=1, minMessage="Product without images") */
    private array $photos = [];

    private ?string $firstImage = '';

    /** @Assert\GreaterThan(value=0, message="Product with zero quantity") */
    private float $quantity = 0.0;

    /** @Assert\NotBlank(message="Product without mpn") */
    private string $mpn = '';

    public function __toString(): string
    {
        return $this->articul . $this->name;
    }

    public function getArticul(): ?string
    {
        return $this->articul;
    }

    public function setArticul(?string $articul): self
    {
        $this->articul = $articul;

        return $this;
    }

    public function setClassif(?int $classif): self
    {
        $this->classif = $classif;

        return $this;
    }

    public function setMesuriment(?int $mesuriment): self
    {
        $this->mesuriment = $mesuriment;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setInfo(string $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function setPhotos(array $photos): self
    {
        $this->photos = $photos;
        $this->firstImage = $photos[0] ?? null;

        return $this;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function setMpn(string $mpn): self
    {
        $this->mpn = $mpn;

        return $this;
    }

    public function getClassif(): ?int
    {
        return $this->classif;
    }

    public function getMesuriment(): ?int
    {
        return $this->mesuriment;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getInfo(): string
    {
        return $this->info;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getPhotos(): array
    {
        return $this->photos;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getMpn(): string
    {
        return $this->mpn;
    }

    public function getFirstImage(): ?string
    {
        return $this->firstImage;
    }

    public function photoCount(): int
    {
        return count($this->photos);
    }

    public function getExtraImages(): array
    {
        return array_slice($this->photos, 1);
    }

    public function getEntityId(): int
    {
        return (int)$this->articul;
    }

    public static function fromArray(array $dataSet): self
    {
        return (new self())
            ->setArticul($dataSet['articul'] ?? '')
            ->setName($dataSet['name'] ?? '')
            ->setPhotos($dataSet['fotos'] ?? [])
            ->setMpn($dataSet['mpn'] ?? '')
            ->setQuantity((float)$dataSet['quantity'] ?? 0.0)
            ->setPrice((float)$dataSet['price'] ?? 0.0)
            ->setInfo($dataSet['info'] ?? '')
            ->setCountry($dataSet['country'] ?? '')
            ->setClassif($dataSet['classif'] ?? null)
            ->setMesuriment($dataSet['mesuriment'] ?? null);
    }
}
