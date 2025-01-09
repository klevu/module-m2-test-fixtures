<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Tax;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

class TaxRuleFixtureRollback
{
    /**
     * @var Registry
     */
    private Registry $registry;
    /**
     * @var TaxRuleRepositoryInterface
     */
    private TaxRuleRepositoryInterface $taxRuleRepository;

    /**
     * @param Registry $registry
     * @param TaxRuleRepositoryInterface $taxRuleRepository
     */
    public function __construct(Registry $registry, TaxRuleRepositoryInterface $taxRuleRepository)
    {
        $this->registry = $registry;
        $this->taxRuleRepository = $taxRuleRepository;
    }

    /**
     * @return TaxRuleFixtureRollback
     */
    public static function create(): TaxRuleFixtureRollback //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            registry: $objectManager->get(Registry::class),
            taxRuleRepository: $objectManager->get(TaxRuleRepositoryInterface::class),
        );
    }

    /**
     * @param TaxRuleFixture ...$taxRuleFixtures
     *
     * @return void
     * @throws \Exception
     */
    public function execute(TaxRuleFixture ...$taxRuleFixtures): void
    {
        $this->registry->unregister(key: 'isSecureArea');
        $this->registry->register(key: 'isSecureArea', value: true);

        foreach ($taxRuleFixtures as $taxRuleFixture) {
            try {
                $this->taxRuleRepository->deleteById(ruleId: $taxRuleFixture->getId());
            } catch (NoSuchEntityException) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
                // tax rate has already been removed
            }
        }

        $this->registry->unregister(key: 'isSecureArea');
    }
}
