<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Test\Integration\Catalog;

use Klevu\TestFixtures\Catalog\ConfigurableProductBuilder;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\ProductBuilder;

class ConfigurableProductBuilderTest extends TestCase
{
    public function testBuild_SingleAttribute_SingleVariant(): void
    {
        $sku = 'configurable-product-builder-test_' . date('YmdHis');
        $attributeCode = 'builder_test_' . date('YmdHis');

        // Chaining used as demo / example code
        $builder = ConfigurableProductBuilder::aConfigurableProduct()
            ->withSku($sku)
            ->withConfigurableAttribute($attributeCode)
            ->withVariant(
                ProductBuilder::aSimpleProduct()
                    ->withPrice(100.00)
                    ->withData([
                        $attributeCode => 'foo',
                    ]),
            );

        /** @var Product $product */
        $product = $builder->build();

        $this->assertInstanceOf(Product::class, $product);
        $this->assertGreaterThan(0, $product->getId());
        $this->assertSame($sku, $product->getSku());
        $this->assertSame(Configurable::TYPE_CODE, $product->getTypeId());

        $regularPrice = $product->getPriceInfo()
            ->getPrice('regular_price');
        $this->assertSame(
            expected: 100.0,
            actual: $regularPrice->getMinRegularAmount()->getValue(),
        );
        $this->assertSame(
            expected: 100.0,
            actual: $regularPrice->getMaxRegularAmount()->getValue(),
        );
    }

    public function testBuild_SingleAttribute_MultipleVariants(): void
    {
        $sku = 'configurable-product-builder-test_' . date('YmdHis');
        $attributeCode = 'builder_test_' . date('YmdHis');

        // Chaining used as demo / example code
        $builder = ConfigurableProductBuilder::aConfigurableProduct()
            ->withSku($sku)
            ->withConfigurableAttribute($attributeCode)
            ->withVariant(
                ProductBuilder::aSimpleProduct()
                    ->withPrice(100.00)
                    ->withData([
                        $attributeCode => 'foo',
                    ]),
            )
            ->withVariant(
                ProductBuilder::aVirtualProduct()
                    ->withPrice(200.00)
                    ->withData([
                        $attributeCode => 'bar',
                    ]),
            );

        /** @var Product $product */
        $product = $builder->build();

        $this->assertInstanceOf(Product::class, $product);
        $this->assertGreaterThan(0, $product->getId());
        $this->assertSame($sku, $product->getSku());
        $this->assertSame(Configurable::TYPE_CODE, $product->getTypeId());

        $regularPrice = $product->getPriceInfo()
            ->getPrice('regular_price');
        $this->assertSame(
            expected: 100.0,
            actual: $regularPrice->getMinRegularAmount()->getValue(),
        );
        $this->assertSame(
            expected: 200.0,
            actual: $regularPrice->getMaxRegularAmount()->getValue(),
        );
    }

    public function testBuild_MultipleAttributes_MultipleVariants(): void
    {
        $sku = 'configurable-product-builder-test_' . date('YmdHis');
        $attributeCode1 = 'builder_test_1_' . date('YmdHis');
        $attributeCode2 = 'builder_test_2_' . date('YmdHis');

        // Chaining used as demo / example code
        $builder = ConfigurableProductBuilder::aConfigurableProduct()
            ->withSku($sku)
            ->withConfigurableAttribute($attributeCode1)
            ->withConfigurableAttribute($attributeCode2)
            ->withVariant(
                ProductBuilder::aSimpleProduct()
                    ->withPrice(100.00)
                    ->withData([
                        $attributeCode1 => 'foo',
                        $attributeCode2 => 'bar',
                    ]),
            )
            ->withVariant(
                ProductBuilder::aVirtualProduct()
                    ->withPrice(200.00)
                    ->withData([
                        $attributeCode1 => 'wom',
                        $attributeCode2 => 'bat',
                    ]),
            );

        /** @var Product $product */
        $product = $builder->build();

        $this->assertInstanceOf(Product::class, $product);
        $this->assertGreaterThan(0, $product->getId());
        $this->assertSame($sku, $product->getSku());
        $this->assertSame(Configurable::TYPE_CODE, $product->getTypeId());

        $regularPrice = $product->getPriceInfo()
            ->getPrice('regular_price');
        $this->assertSame(
            expected: 100.0,
            actual: $regularPrice->getMinRegularAmount()->getValue(),
        );
        $this->assertSame(
            expected: 200.0,
            actual: $regularPrice->getMaxRegularAmount()->getValue(),
        );
    }
}
