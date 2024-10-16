<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Repository\ProductRepository")
 * @ORM\Table(name="oc_product")
 * @ORM\HasLifecycleCallbacks
 */
class Product
{
    public const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
    public const MYSQL_DATE_FORMAT = 'Y-m-d';

    /**
     * Отсутствие на складе
     *
     * 7 - В наличии
     * 8 - Предзаказ
     * 5 - Нет в наличии
     * 6 - Ожидание 2-3 дня
     */
    private const STOCK_STATUS_ID = 5;

    /**
     * Необходима доставка
     * 1 - true
     * 2 - false
     */
    private const SHIPPING = 1;

    /**
     * Единица измерения веса
     * 1 - кг
     * 2 - грамм
     */
    private const WEITH_CLASS_ID = 1;

    /**
     * Единица измерения длинны
     * 1 - см
     * 2 - миллиметр
     */
    private const LENGTH_CLASS_ID = 1;

    /**
     * Вычитать со склада
     * 1 - true
     * 2 - false
     */
    private const SUBTRACT = 1;

    /**
     * Минимальное кол-во для заказа
     */
    private const MINIMUM = 1;

    /**
     * Статус
     * 1 - Включено
     * 2 - Выключено
     */
    private const STATUS = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="product_id")
     */
    private ?int $productId = null;

    /**
     * @ORM\Column(name="model", type="string", length=64, nullable=false)
     * @Groups({"sm_import"})
     */
    private string $model = '';

    /**
     * @ORM\Column(name="sku", type="string", length=64, nullable=false)
     * @Groups({"sm_import"})
     */
    private string $sku = '';

    /**
     * @ORM\Column(name="upc", type="string", length=12, nullable=false)
     * @Groups({"sm_import"})
     */
    private string $upc = '';

    /**
     * @ORM\Column(name="ean", type="string", length=14, nullable=false)
     * @Groups({"sm_import"})
     */
    private string $ean = '';

    /**
     * @ORM\Column(name="jan", type="string", length=13, nullable=false)
     * @Groups({"sm_import"})
     */
    private string $jan = '';

    /**
     * @ORM\Column(name="isbn", type="string", length=17, nullable=false)
     * @Groups({"sm_import"})
     */
    private string $isbn = '';

    /**
     * @ORM\Column(name="mpn", type="text", nullable=false)
     * @Groups({"sm_import"})
     */
    private string $mpn = '';

    /**
     * @ORM\Column(name="location", type="string", length=128, nullable=false)
     * @Groups({"sm_import"})
     */
    private string $location = '';

    /**
     * @ORM\Column(name="quantity", type="integer", nullable=false)
     * @Groups({"sm_import"})
     */
    private int $quantity = 0;

    /**
     * @ORM\Column(name="stock_status_id", type="integer", nullable=false)
     * @Groups({"sm_import"})
     */
    private int $stockStatusId = 0;

    /**
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     * @Groups({"sm_import"})
     */
    private ?string $image = null;

    /**
     * @ORM\Column(name="manufacturer_id", type="integer", nullable=false)
     * @Groups({"sm_import"})
     */
    private int $manufacturerId = 0;

    /**
     * @ORM\Column(name="shipping", type="smallint", nullable=false)
     * @Groups({"sm_import"})
     */
    private int $shipping = 0;

    /**
     * @ORM\Column(name="price", type="float", nullable=false)
     * @Groups({"sm_import"})
     */
    private float $price = 0.0;

    /**
     * @ORM\Column(name="points", type="integer", nullable=false)
     * @Groups({"sm_import"})
     */
    private int $points = 0;

    /**
     * @ORM\Column(name="tax_class_id", type="integer", nullable=false)
     * @Groups({"sm_import"})
     */
    private int $taxClassId = 0;

    /**
     * @ORM\Column(name="date_available", type="date", nullable=false)
     * @Groups({"sm_import"})
     * @Context(
     *     context={DateTimeNormalizer::FORMAT_KEY, self::MYSQL_DATE_FORMAT},
     *     groups={"sm_import"}
     * )
     */
    private DateTime $dateAvailable;

    /**
     * @ORM\Column(name="weight", type="float", nullable=false)
     * @Groups({"sm_import"})
     */
    private float $weight = 0.0;

    /**
     * @ORM\Column(name="weight_class_id", type="integer", nullable=false)
     * @Groups({"sm_import"})
     */
    private int $weightClassId = 0;

    /**
     * @ORM\Column(name="length", type="float", nullable=false)
     * @Groups({"sm_import"})
     */
    private float $length = 0.0;

    /**
     * @ORM\Column(name="width", type="float", nullable=false)
     * @Groups({"sm_import"})
     */
    private float $width = 0.0;

    /**
     * @ORM\Column(name="height", type="float", nullable=false)
     * @Groups({"sm_import"})
     */
    private float $height = 0.0;

    /**
     * @ORM\Column(name="length_class_id", type="integer", nullable=false)
     * @Groups({"sm_import"})
     */
    private int $lengthClassId = 0;

    /**
     * @ORM\Column(name="subtract", type="smallint", nullable=false)
     * @Groups({"sm_import"})
     */
    private int $subtract = 0;

    /**
     * @ORM\Column(name="minimum", type="integer", nullable=false)
     * @Groups({"sm_import"})
     */
    private int $minimum = 0;

    /**
     * @ORM\Column(name="sort_order", type="integer", nullable=false)
     */
    private int $sortOrder = 0;

    /**
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    private int $status = 0;

    /**
     * @ORM\Column(name="viewed", type="integer", nullable=false)
     */
    private int $viewed = 0;

    /**
     * @ORM\Column(name="date_added", nullable=false)
     */
    private DateTimeImmutable $dateAdded;

    /**
     * @ORM\Column(name="date_modified", nullable=false)
     */
    private DateTimeImmutable $dateModified;

    /**
     * @ORM\Column(name="viewed_mmlivesearch", type="integer", nullable=false)
     * @Groups({"sm_import"})
     */
    private int $viewedMmlivesearch = 0;

    /**
     * @ORM\Column(name="special", type="float", nullable=false)
     * @Groups({"sm_import"})
     */
    private float $special = 0.0;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ExtraImage", mappedBy="product")
     */
    private Collection $extraImages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductDescription",
     *     mappedBy="product", cascade={"persist", "remove"}, orphanRemoval=true
     * )
     */
    private Collection $descriptions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductOptionValue",
     *     mappedBy="product", cascade={"persist"}
     * )
     */
    private Collection $productOptionValues;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductOption",
     *     mappedBy="product", cascade={"persist"}
     * )
     */
    private Collection $productOptions;

    /**
     * @ORM\JoinTable(name="oc_product_to_category",
     *     joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="product_id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="category_id")}
     * )
     * @ORM\ManyToMany(targetEntity="App\Entity\Category")
     */
    private Collection $categories;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProductToStore",
     *     mappedBy="product"
     * )
     */
    private Collection $stores;

    public function __construct()
    {
        $this->dateAvailable = new DateTime('0001-01-01');
        $this->dateAdded = new DateTimeImmutable();
        $this->dateModified = new DateTimeImmutable();
        $this->extraImages = new ArrayCollection();
        $this->descriptions = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->stores = new ArrayCollection();

        // set meaningfully default values
        $this->stockStatusId = self::STOCK_STATUS_ID;
        $this->shipping = self::SHIPPING;
        $this->weightClassId = self::WEITH_CLASS_ID;
        $this->lengthClassId = self::LENGTH_CLASS_ID;
        $this->subtract = self::SUBTRACT;
        $this->status = self::STATUS;
        $this->minimum = self::MINIMUM;
    }

    /**
     * @ORM\PreUpdate
     */
    public function renewDateModified(): void
    {
        $this->dateModified = new DateTimeImmutable();
    }

    // ... Getter and Setter methods ...

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getUpc(): string
    {
        return $this->upc;
    }

    public function setUpc(string $upc): self
    {
        $this->upc = $upc;

        return $this;
    }

    public function getEan(): string
    {
        return $this->ean;
    }

    public function setEan(string $ean): self
    {
        $this->ean = $ean;

        return $this;
    }

    public function getJan(): string
    {
        return $this->jan;
    }

    public function setJan(string $jan): self
    {
        $this->jan = $jan;

        return $this;
    }

    public function getIsbn(): string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): self
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getMpn(): string
    {
        return $this->mpn;
    }

    public function setMpn(string $mpn): self
    {
        $this->mpn = $mpn;

        return $this;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

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

    public function getStockStatusId(): int
    {
        return $this->stockStatusId;
    }

    public function setStockStatusId(int $stockStatusId): self
    {
        $this->stockStatusId = $stockStatusId;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getManufacturerId(): int
    {
        return $this->manufacturerId;
    }

    public function setManufacturerId(int $manufacturerId): self
    {
        $this->manufacturerId = $manufacturerId;

        return $this;
    }

    public function getShipping(): int
    {
        return $this->shipping;
    }

    public function setShipping(int $shipping): self
    {
        $this->shipping = $shipping;

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

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): self
    {
        $this->points = $points;

        return $this;
    }

    public function getTaxClassId(): int
    {
        return $this->taxClassId;
    }

    public function setTaxClassId(int $taxClassId): self
    {
        $this->taxClassId = $taxClassId;

        return $this;
    }

    public function getDateAvailable(): DateTime
    {
        return $this->dateAvailable;
    }

    public function setDateAvailable(DateTime $dateAvailable): self
    {
        $this->dateAvailable = $dateAvailable;

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

    public function getWeightClassId(): int
    {
        return $this->weightClassId;
    }

    public function setWeightClassId(int $weightClassId): self
    {
        $this->weightClassId = $weightClassId;

        return $this;
    }

    public function getLength(): float
    {
        return $this->length;
    }

    public function setLength(float $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function setWidth(float $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function setHeight(float $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getLengthClassId(): int
    {
        return $this->lengthClassId;
    }

    public function setLengthClassId(int $lengthClassId): self
    {
        $this->lengthClassId = $lengthClassId;

        return $this;
    }

    public function getSubtract(): int
    {
        return $this->subtract;
    }

    public function setSubtract(int $subtract): self
    {
        $this->subtract = $subtract;

        return $this;
    }

    public function getMinimum(): int
    {
        return $this->minimum;
    }

    public function setMinimum(int $minimum): self
    {
        $this->minimum = $minimum;

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

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getViewed(): int
    {
        return $this->viewed;
    }

    public function setViewed(int $viewed): self
    {
        $this->viewed = $viewed;

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

    public function getViewedMmlivesearch(): int
    {
        return $this->viewedMmlivesearch;
    }

    public function setViewedMmlivesearch(int $viewedMmlivesearch): self
    {
        $this->viewedMmlivesearch = $viewedMmlivesearch;

        return $this;
    }

    public function getSpecial(): float
    {
        return $this->special;
    }

    public function setSpecial(float $special): self
    {
        $this->special = $special;

        return $this;
    }

    public function getExtraImages(): Collection
    {
        if ($this->extraImages) {
            return $this->extraImages;
        }

        return new ArrayCollection();
    }

    public function setExtraImages(Collection $extraImages): self
    {
        $this->extraImages = $extraImages;

        return $this;
    }

    public function addExtraImage(string $image): ExtraImage
    {
        if ($this->hasExtraImage($image)) {
            return $this->extraImages->findFirst(
                fn($extra): bool => $image == $extra->getImage()
            );
        }

        $extraImage = (new ExtraImage())->setImage($image);
        $extraImage->setProduct($this);
        $this->extraImages[] = $extraImage;

        return $extraImage;
    }

    public function hasExtraImage(string $image): bool
    {
        foreach ($this->extraImages as $extra) {
            if ($extra->getImage() === $image) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection|ProductDescription[]
     */
    public function getDescriptions(): Collection
    {
        return $this->descriptions;
    }

    public function setDescriptions(Collection $descriptions): self
    {
        $this->descriptions = $descriptions;

        return $this;
    }

    public function hasDescription(ProductDescription $check): bool
    {
        $found = $this->descriptions->filter(
            fn($desc) => (
                    $this->productId === $check->getProduct()->getProductId())
                && ($desc->getLanguageId() === $check->getLanguageId()
                )
        );

        if ($found->count() > 0) {
            return true;
        }

        return false;
    }

    public function getDescriptionByKey(int $languageId): ?ProductDescription
    {
        $found = $this->descriptions->filter(
            fn($desc) => $desc->getLanguageId() === $languageId
        );

        if ($found->count() > 0) {
            return $found->first();
        }

        return null;
    }

    public function addDescription(ProductDescription $desc): void
    {
        if (!$this->hasDescription($desc)) {
            $this->descriptions[] = $desc;
        }
    }

    public function getExtraImage(string $image): ?ExtraImage
    {
        $extraImages = $this->getExtraImages();

        if (count($extraImages) < 1) {
            return null;
        }

        $found = $extraImages->filter(
            fn($ext) => $ext->getImage() === $image
        );

        if ($found->count() > 0) {
            return $found->first();
        }

        return null;
    }

    public function addStore(ProductToStore $store): self
    {
        $this->stores = new ArrayCollection([$store]);

        return $this;
    }

    public function getStore(): ?ProductToStore
    {
        $founded = $this->stores->first();

        if ($founded) {
            return $founded;
        }

        return null;
    }

    public function addCategory(Category $category): self
    {
//        $category->addProduct($this);
        $this->categories[] = $category;

        return $this;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function setCategories(Collection $categories): self
    {
        $this->categories = $categories;

        return $this;
    }
}
