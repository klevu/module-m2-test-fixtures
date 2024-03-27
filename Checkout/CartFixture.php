<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Checkout;

use Magento\Quote\Api\Data\CartInterface;

class CartFixture
{
    /**
     * @var CartInterface
     */
    private CartInterface $cart;

    /**
     * @param CartInterface $cart
     */
    public function __construct(CartInterface $cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return CartInterface
     */
    public function getCart(): CartInterface
    {
        return $this->cart;
    }

    /**
     * @return int
     */
    public function getCartId(): int
    {
        return (int)$this->cart->getId();
    }

    /**
     * @return void
     */
    public function rollback(): void
    {
        CartFixtureRollback::create()->execute($this);
    }
}
