<?php

declare(strict_types=1);

namespace App\Tests\Unit\DTO;

use App\DTO\ProductDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ProductDtoTest extends TestCase
{
    public function testValidProduct(): void
    {
        $product = (new ProductDto())
            ->setArticul('12345')
            ->setClassif(1)
            ->setCountry('Country')
            ->setName('Title of product')
            ->setInfo('Description of product')
            ->setMpn('123')
            ->setPrice(123.50)
            ->setQuantity(40.55)
            ->setPhotos(['first.jpg']);

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $errors = $validator->validate($product);

        $this->assertSame($errors->count(), 0);
    }

    public function testWithErrors(): void
    {
        $product = (new ProductDto());

        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $errors = $validator->validate($product);

        $this->assertSame($errors->count(), 9);
    }
}
