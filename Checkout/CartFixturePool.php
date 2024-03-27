<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Checkout;

use Magento\Quote\Api\Data\CartInterface;

class CartFixturePool
{
    /**
     * @var CartFixture[]
     */
    private array $cartFixtures = [];

    /**
     * @param CartInterface $cart
     * @param string|null $key
     *
     * @return void
     */
    public function add(CartInterface $cart, ?string $key = null): void
    {
        if ($key === null) {
            $this->cartFixtures[] = new CartFixture($cart);
        } else {
            $this->cartFixtures[$key] = new CartFixture($cart);
        }
    }

    /**
     * @param string|null $key
     *
     * @return CartFixture
     */
    public function get(?string $key = null): CartFixture
    {
        if ($key === null) {
            $key = array_key_last($this->cartFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->cartFixtures)) {
            throw new \OutOfBoundsException('No matching cart found in fixture pool');
        }

        return $this->cartFixtures[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        CartFixtureRollback::create()->execute(...array_values($this->cartFixtures));
        $this->cartFixtures = [];
    }
}
