<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

//phpcs:disable Magento2.Annotation.MethodArguments.ArgumentMissing

namespace Klevu\TestFixtures\Store;

use Klevu\TestFixtures\Exception\InvalidModelException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Model\ResourceModel\Group as StoreGroupResourceModel;
use Magento\TestFramework\Helper\Bootstrap;

class StoreGroupFixtureRollback
{
    /**
     * @var Registry
     */
    private Registry $registry;
    /**
     * @var GroupRepositoryInterface
     */
    private GroupRepositoryInterface $storeGroupRepository;

    /**
     * @param Registry $registry
     * @param GroupRepositoryInterface $storeGroupRepository
     */
    public function __construct(
        Registry $registry,
        GroupRepositoryInterface $storeGroupRepository,
    ) {
        $this->registry = $registry;
        $this->storeGroupRepository = $storeGroupRepository;
    }

    /**
     * @return StoreGroupFixtureRollback
     */
    public static function create(): StoreGroupFixtureRollback //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(GroupRepositoryInterface::class),
        );
    }

    /**
     * Roll back store groups.
     *
     * @param StoreGroupFixture ...$storeGroupFixtures
     *
     * @throws InvalidModelException
     * @throws \Exception
     */
    public function execute(StoreGroupFixture ...$storeGroupFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($storeGroupFixtures as $storeGroupFixture) {
            try {
                $storeGroup = $this->storeGroupRepository->get((string)$storeGroupFixture->getId());
                if (!method_exists($storeGroup, 'getResource')) {
                    throw new InvalidModelException(
                        sprintf(
                            'Provided Model %s does not have require method %s.',
                            $storeGroup::class,
                            'getResource',
                        ),
                    );
                }
                // store repository has no delete methods so revert to resourceModel
                $storeGroupResourceModel = $storeGroup->getResource();
                if (!($storeGroupResourceModel instanceof StoreGroupResourceModel)) {
                    throw new InvalidModelException(
                        sprintf(
                            'Resource Model %s is not an instance of %s.',
                            $storeGroupResourceModel::class,
                            StoreGroupResourceModel::class,
                        ),
                    );
                }
                $storeGroupResourceModel->delete($storeGroup);
            } catch (NoSuchEntityException) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                // store group has already been removed
            }
        }

        $this->registry->unregister('isSecureArea');
    }
}
