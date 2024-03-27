<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

//phpcs:disable Magento2.Annotation.MethodArguments.ArgumentMissing

namespace Klevu\TestFixtures\Catalog\Rule;

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

class RuleFixtureRollback
{
    /**
     * @var Registry
     */
    private Registry $registry;
    /**
     * @var CatalogRuleRepositoryInterface
     */
    private CatalogRuleRepositoryInterface $catalogRuleRepository;

    /**
     * @param Registry $registry
     * @param CatalogRuleRepositoryInterface $catalogRuleRepository
     */
    public function __construct(
        Registry $registry,
        CatalogRuleRepositoryInterface $catalogRuleRepository,
    ) {
        $this->registry = $registry;
        $this->catalogRuleRepository = $catalogRuleRepository;
    }

    /**
     * @return RuleFixtureRollback
     */
    public static function create(): RuleFixtureRollback //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->get(Registry::class),
            $objectManager->get(CatalogRuleRepositoryInterface::class),
        );
    }

    /**
     * Roll back attributes.
     *
     * @param RuleFixture ...$ruleFixtures
     *
     * @throws CouldNotDeleteException
     * @throws \Exception
     */
    public function execute(RuleFixture ...$ruleFixtures): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach ($ruleFixtures as $ruleFixture) {
            $this->catalogRuleRepository->deleteById(
                $ruleFixture->getRuleId(),
            );
        }

        $this->registry->unregister('isSecureArea');
    }
}
