<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Checkout;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

trait CartTrait
{
    /**
     * @var CartFixturePool
     */
    private mixed $cartFixturePool = null;

    /**
     * $cartData = [
     *   'products' => [
     *     'simple' => [
     *        'SKU001' => 1, // 'sku' => 'qty'
     *     ],
     *     'configurable' => [
     *       'SKU002' => [
     *         'qty' => 1,
     *         'options' => [
     *           'configurable_attribute_code' => 12345, // 'sku' => 'optionId'
     *          ],
     *        ],
     *     ],
     *     'grouped' => [
     *       'SKU_003' => [
     *          'qty' => 1,
     *          'options' => [
     *             'SKU_004' => 1, // sku => qty
     *             'SKU_005' => 2,
     *           ],
     *        ],
     *     ],
     *   ],
     * ]
     *
     * @param mixed[]|null $cartData
     *
     * @return void
     * @throws LocalizedException
     */
    public function createCart(?array $cartData = []): void
    {
        $cartBuilder = CartBuilder::forCurrentSession();
        $cartBuilder = $cartBuilder->withAddress(address: $cartData['address'] ?? []);

        if ($cartData['store_id'] ?? null) {
            $cartBuilder = $cartBuilder->withStoreId(storeId: $cartData['store_id']);
        }
        if ($cartData['reserved_order_id'] ?? null) {
            $cartBuilder = $cartBuilder->withReservedOrderId(reservedOrderId: $cartData['reserved_order_id']);
        }
        foreach (($cartData['products'] ?? []) as $type => $cartItemData) {
            $cartBuilder = match ($type) {
                Configurable::TYPE_CODE => $this->buildConfigurableProduct($cartItemData, $cartBuilder),
                Grouped::TYPE_CODE => $this->buildGroupedProduct($cartItemData, $cartBuilder),
                default => $this->buildDefaultProduct($cartItemData, $cartBuilder),
            };
        }

        $this->cartFixturePool->add(
            cart: $cartBuilder->build(),
            key: $cartData['key'] ?? 'test_cart',
        );
    }

    /**
     * @param mixed[] $cartItemData
     * @param CartBuilder $cartBuilder
     *
     * @return CartBuilder
     */
    private function buildConfigurableProduct(array $cartItemData, CartBuilder $cartBuilder): CartBuilder
    {
        foreach ($cartItemData as $sku => $data) {
            $cartBuilder = $cartBuilder->withConfigurableProduct(
                sku: $sku,
                options: $data['options'],
                qty: $data['qty'] ?? null,
            );
        }

        return $cartBuilder;
    }

    /**
     * @param mixed[] $cartItemData
     * @param CartBuilder $cartBuilder
     *
     * @return CartBuilder
     */
    private function buildGroupedProduct(array $cartItemData, CartBuilder $cartBuilder): CartBuilder
    {
        foreach ($cartItemData as $sku => $data) {
            $cartBuilder = $cartBuilder->withGroupedProduct(
                sku: $sku,
                options: $data['options'],
                qty: $data['qty'] ?? null,
            );
        }

        return $cartBuilder;
    }

    /**
     * @param mixed[] $cartItemData
     * @param CartBuilder $cartBuilder
     *
     * @return CartBuilder
     */
    private function buildDefaultProduct(array $cartItemData, CartBuilder $cartBuilder): CartBuilder
    {
        foreach ($cartItemData as $sku => $qty) {
            $cartBuilder = $cartBuilder->withSimpleProduct(sku: $sku, qty: $qty);
        }

        return $cartBuilder;
    }
}
