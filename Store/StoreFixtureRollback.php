<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

//phpcs:disable Magento2.Annotation.MethodArguments.ArgumentMissing

namespace Klevu\TestFixtures\Store;

use Klevu\Configuration\Service\Provider\ScopeProviderInterface;
use Klevu\TestFixtures\Exception\InvalidModelException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ResourceModel\Store as StoreResourceModel;
use Magento\TestFramework\Helper\Bootstrap;

class StoreFixtureRollback
{
    /**
     * @var Registry
     */
    private Registry $registry;
    /**
     * @var StoreRepositoryInterface
     */
    private StoreRepositoryInterface $storeRepository;
    /**
     * @var ScopeProviderInterface
     */
    private ScopeProviderInterface $scopeProvider;

    /**
     * @param Registry $registry
     * @param StoreRepositoryInterface $storeRepository
     * @param ScopeProviderInterface $scopeProvider
     */
    public function __construct(
        Registry $registry,
        StoreRepositoryInterface $storeRepository,
        ScopeProviderInterface $scopeProvider,
    ) {
        $this->registry = $registry;
        $this->storeRepository = $storeRepository;
        $this->scopeProvider = $scopeProvider;
    }

    /**
     * @return StoreFixtureRollback
     */
    public static function create(): StoreFixtureRollback //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(StoreRepositoryInterface::class),
            $objectManager->get(ScopeProviderInterface::class),
        );
    }

    /**
     * Roll back stores.
     *
     * @param StoreFixture ...$storeFixtures
     *
     * @throws InvalidModelException
     * @throws \Exception
     */
    public function execute(StoreFixture ...$storeFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($storeFixtures as $storeFixture) {
            try {
                $store = $this->storeRepository->get((string)$storeFixture->getId());
                if (!method_exists($store, 'getResource')) {
                    throw new InvalidModelException(
                        sprintf(
                            'Provided Model %s does not have require method %s.',
                            $store::class,
                            'getResource',
                        ),
                    );
                }
                // store repository has no delete methods so revert to resourceModel
                $storeResourceModel = $store->getResource();
                if (!($storeResourceModel instanceof StoreResourceModel)) {
                    throw new InvalidModelException(
                        sprintf(
                            'Resource Model %s is not an instance of %s.',
                            $storeResourceModel::class,
                            StoreResourceModel::class,
                        ),
                    );
                }
                $storeResourceModel->delete($store);
                $scope = $this->scopeProvider->getCurrentScope();
                if (
                    $scope->getScopeObject() instanceof StoreInterface
                    && $scope->getScopeId() === $storeFixture->getId()
                ) {
                    $this->scopeProvider->unsetCurrentScope();
                }
            } catch (NoSuchEntityException) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                // store has already been removed
            }
        }

        $this->registry->unregister('isSecureArea');
    }
}
