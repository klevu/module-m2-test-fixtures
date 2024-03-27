<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Checkout;

use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

class CartFixtureRollback
{
    /**
     * @var Registry
     */
    private Registry $registry;
    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @param Registry $registry
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        Registry $registry,
        CartRepositoryInterface $cartRepository,
    ) {
        $this->registry = $registry;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @return CartFixtureRollback
     */
    public static function create(): CartFixtureRollback //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(CartRepositoryInterface::class),
        );
    }

    /**
     * @param CartFixture ...$cartFixtures
     *
     * @return void
     */
    public function execute(CartFixture ...$cartFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($cartFixtures as $cartFixture) {
            $this->cartRepository->delete($cartFixture->getCart());
        }

        $this->registry->unregister('isSecureArea');
    }
}
