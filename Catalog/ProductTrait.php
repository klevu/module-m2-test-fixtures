<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog;

use Klevu\TestFixtures\Catalog\Product\ConfigurableProductBuilder;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Catalog\ProductFixture;
use TddWizard\Fixtures\Catalog\ProductFixturePool;

trait ProductTrait
{
    /**
     * @var ProductFixturePool
     */
    private mixed $productFixturePool = [];

    /**
     * Example usage setting store level data
     * $this->createProduct(
     *   productData: [
     *     'status' => Status::STATUS_ENABLED,
     *     'stores' => [
     *        $store1->getId() => [
     *          'status' => Status::STATUS_ENABLED,
     *        ],
     *        $store2->getId() => [
     *          'status' => Status::STATUS_DISABLED,
     *        ],
     *      ],
     *   ],
     * );
     *
     * @param mixed[] $productData
     * @param int|null $storeId
     *
     * @return void
     * @throws \Exception
     */
    public function createProduct(array $productData = [], ?int $storeId = null): void // phpcs:ignore SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh, Generic.Files.LineLength.TooLong
    {
        $productBuilder = $this->getProductBuilder($productData['type_id'] ?? null);

        if (!empty($productData['sku'])) {
            $productBuilder = $productBuilder->withSku($productData['sku']);
        }
        if (!empty($productData['name'])) {
            $productBuilder = $productBuilder->withName($productData['name'], $storeId);
        }
        if (isset($productData['status'])) {
            // we pass store here, but remember that this is a website scope setting
            $productBuilder = $productBuilder->withStatus($productData['status'], $storeId);
        }
        if (!empty($productData['visibility'])) {
            $productBuilder = $productBuilder->withVisibility($productData['visibility'], $storeId);
        }
        if (isset($productData['in_stock'])) {
            $productBuilder = $productBuilder->withIsInStock($productData['in_stock']);
        }
        if (isset($productData['qty'])) {
            $productBuilder = $productBuilder->withStockQty($productData['qty']);
        }
        if (isset($productData['backorders'])) {
            $productBuilder = $productBuilder->withBackorders($productData['backorders']);
        }
        if (isset($productData['price'])) {
            $productBuilder = $productBuilder->withPrice($productData['price']);
        }
        if (isset($productData['tier_prices'])) {
            /**
             * $productData['tier_prices']['price' => 10.00, 'qty' => 1, 'customer_group' => 2]
             */
            $productBuilder = $productBuilder->withTierPrices($productData['tier_prices']);
        }
        if (isset($productData['tax_class_id'])) {
            $productBuilder = $productBuilder->withTaxClassId($productData['tax_class_id']);
        }
        if (!empty($productData['website_ids'])) {
            $productBuilder = $productBuilder->withWebsiteIds($productData['website_ids']);
        }
        if (!empty($productData['category_ids'])) {
            $productBuilder = $productBuilder->withCategoryIds($productData['category_ids']);
        }
        if (!empty($productData['custom_attributes'])) {
            $productBuilder = $productBuilder->withCustomAttributes($productData['custom_attributes'], $storeId);
        }
        if (!empty($productData['images'])) {
            /**
             * key of $productData['images'] array is image type,
             * e.g. image, small_image, thumbnail, klevu_image
             * will default to image if not supplied
             */
            foreach ($productData['images'] as $type => $image) {
                if (is_numeric($type)) {
                    $type = null;
                }
                $productBuilder = $productBuilder->withImage(fileName: $image, imageType: $type);
            }
        }
        if (!empty($productData['data'])) {
            $productBuilder = $productBuilder->withData($productData['data']);
        }
        if (($productData['type_id'] ?? null) === Grouped::TYPE_CODE) {
            // grouped product
            if (!empty($productData['linked_products'])) {
                $productBuilder = $this->processLinkedProduct($productBuilder, $productData['linked_products']);
            }
        }
        if (($productData['type_id'] ?? null) === Configurable::TYPE_CODE) {
            // configurable products
            if (!empty($productData['configurable_attributes'])) {
                $productBuilder = $this->processConfigurableAttributes(
                    $productBuilder,
                    $productData['configurable_attributes'],
                );
            }
            if (!empty($productData['variants'])) {
                $productBuilder = $this->processVariants($productBuilder, $productData['variants']);
            }
        }
        if (($productData['type_id'] ?? null) === DownloadableType::TYPE_DOWNLOADABLE) {
            if (!empty($productData['download_links'])) {
                $productBuilder = $productBuilder->withDownloadLinks($productData['download_links']);
            }
        }
        if (!empty($productData['stores'])) {
            $productBuilder = $this->setStoreLevelData($productData['stores'], $productBuilder);
        }

        $this->productFixturePool->add(
            product: $productBuilder->build(),
            key: $productData['key'] ?? 'test_product',
        );
    }

    /**
     * @param string|null $typeId
     *
     * @return ProductBuilder
     */
    private function getProductBuilder(?string $typeId = null): ProductBuilder
    {
        // @TODO add bundle products & gift cards
        return match ($typeId ?? null) {
            DownloadableType::TYPE_DOWNLOADABLE => ProductBuilder::aDownloadableProduct(),
            Type::TYPE_VIRTUAL => ProductBuilder::aVirtualProduct(),
            Grouped::TYPE_CODE => GroupedProductBuilder::aGroupedProduct(),
            Configurable::TYPE_CODE => ConfigurableProductBuilder::aConfigurableProduct(),
            default => ProductBuilder::aSimpleProduct(),
        };
    }

    /**
     * @param ProductBuilder $productBuilder
     * @param ProductFixture[] $linkedProductFixtures
     *
     * @return ProductBuilder
     */
    private function processLinkedProduct(
        ProductBuilder $productBuilder,
        array $linkedProductFixtures,
    ): ProductBuilder {
        foreach ($linkedProductFixtures as $linkedProductFixture) {
            /** @var GroupedProductBuilder $productBuilder */
            $productBuilder = $productBuilder->withLinkedProductFixtures(
                linkedProductFixture: $linkedProductFixture,
            );
        }

        return $productBuilder;
    }

    /**
     * @param ProductBuilder $productBuilder
     * @param AttributeInterface[] $configurableAttributes
     *
     * @return ProductBuilder
     */
    private function processConfigurableAttributes(
        ProductBuilder $productBuilder,
        array $configurableAttributes,
    ): ProductBuilder {
        foreach ($configurableAttributes as $attribute) {
            /** @var ConfigurableProductBuilder $productBuilder */
            $productBuilder = $productBuilder->withConfigurableAttribute(
                attribute: $attribute,
            );
        }

        return $productBuilder;
    }

    /**
     * @param ProductBuilder $productBuilder
     * @param ProductInterface[] $variantProducts
     *
     * @return ProductBuilder
     */
    private function processVariants(
        ProductBuilder $productBuilder,
        array $variantProducts,
    ): ProductBuilder {
        foreach ($variantProducts as $variantProduct) {
            /** @var ConfigurableProductBuilder $productBuilder */
            $productBuilder = $productBuilder->withVariant($variantProduct);
        }

        return $productBuilder;
    }

    /**
     * @param mixed[] $storesData
     * @param ProductBuilder $productBuilder
     *
     * @return ProductBuilder
     */
    private function setStoreLevelData(array $storesData, ProductBuilder $productBuilder): ProductBuilder
    {
        foreach ($storesData as $storeIdKey => $productStoreData) {
            if (!empty($productStoreData['name'])) {
                $productBuilder = $productBuilder->withName(
                    $productStoreData['name'],
                    $storeIdKey,
                );
            }
            if (isset($productStoreData['status'])) {
                // we pass store here, but remember that this is a website scope setting
                $productBuilder = $productBuilder->withStatus(
                    $productStoreData['status'],
                    $storeIdKey,
                );
            }
            if (!empty($productStoreData['visibility'])) {
                $productBuilder = $productBuilder->withVisibility(
                    $productStoreData['visibility'],
                    $storeIdKey,
                );
            }
            if (!empty($productStoreData['custom_attributes'])) {
                $productBuilder = $productBuilder->withCustomAttributes(
                    $productStoreData['custom_attributes'],
                    $storeIdKey,
                );
            }
        }

        return $productBuilder;
    }
}
