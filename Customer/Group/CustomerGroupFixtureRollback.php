<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Customer\Group;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

class CustomerGroupFixtureRollback
{
    /**
     * @var Registry
     */
    private Registry $registry;
    /**
     * @var GroupRepositoryInterface
     */
    private GroupRepositoryInterface $customerGroupRepository;

    /**
     * @param Registry $registry
     * @param GroupRepositoryInterface $customerGroupRepository
     */
    public function __construct(
        Registry $registry,
        GroupRepositoryInterface $customerGroupRepository,
    ) {
        $this->registry = $registry;
        $this->customerGroupRepository = $customerGroupRepository;
    }

    /**
     * @return CustomerGroupFixtureRollback
     */
    public static function create(): CustomerGroupFixtureRollback //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(GroupRepositoryInterface::class),
        );
    }

    /**
     * @param CustomerGroupFixture ...$customerGroupFixtures
     *
     * @return void
     * @throws LocalizedException
     * @throws StateException
     */
    public function execute(CustomerGroupFixture ...$customerGroupFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($customerGroupFixtures as $customerGroupFixture) {
            try {
                $this->customerGroupRepository->deleteById((int)$customerGroupFixture->getId());
            } catch (NoSuchEntityException) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                // customer group has already been removed
            }
        }
        $this->registry->unregister('isSecureArea');
    }
}
