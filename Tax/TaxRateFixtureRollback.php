<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Tax;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

class TaxRateFixtureRollback
{
    /**
     * @var Registry
     */
    private Registry $registry;
    /**
     * @var TaxRateRepositoryInterface
     */
    private TaxRateRepositoryInterface $taxRateRepository;

    /**
     * @param Registry $registry
     * @param TaxRateRepositoryInterface $taxRateRepository
     */
    public function __construct(Registry $registry, TaxRateRepositoryInterface $taxRateRepository)
    {
        $this->registry = $registry;
        $this->taxRateRepository = $taxRateRepository;
    }

    /**
     * @return TaxRateFixtureRollback
     */
    public static function create(): TaxRateFixtureRollback //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            registry: $objectManager->get(Registry::class),
            taxRateRepository: $objectManager->get(TaxRateRepositoryInterface::class),
        );
    }

    /**
     * @param TaxRateFixture ...$taxRateFixtures
     *
     * @return void
     * @throws \Exception
     */
    public function execute(TaxRateFixture ...$taxRateFixtures): void
    {
        $this->registry->unregister(key: 'isSecureArea');
        $this->registry->register(key: 'isSecureArea', value: true);

        foreach ($taxRateFixtures as $taxRateFixture) {
            try {
                $this->taxRateRepository->deleteById(rateId: $taxRateFixture->getId());
            } catch (NoSuchEntityException) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                // tax rate has already been removed
            }
        }

        $this->registry->unregister(key: 'isSecureArea');
    }
}
