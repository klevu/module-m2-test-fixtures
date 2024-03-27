<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\Rule;

use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Api\Data\ConditionInterface;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Model\Data\ConditionFactory;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\Rule\Condition\Combine as CombineCondition;
use Magento\CatalogRule\Model\Rule\Condition\Product as ProductCondition;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\TestFramework\Helper\Bootstrap;
use TddWizard\Fixtures\Catalog\IndexFailed;

class RuleBuilder
{
    /**
     * @var Rule
     */
    private RuleInterface $rule;
    /**
     * @var CatalogRuleRepositoryInterface
     */
    private CatalogRuleRepositoryInterface $catalogRuleRepository;
    /**
     * @var ConditionFactory
     */
    private ConditionFactory $conditionFactory;

    /**
     * @param RuleInterface $rule
     * @param CatalogRuleRepositoryInterface $catalogRuleRepository
     * @param ConditionFactory $conditionFactory
     */
    public function __construct(
        RuleInterface $rule,
        CatalogRuleRepositoryInterface $catalogRuleRepository,
        ConditionFactory $conditionFactory,
    ) {
        $this->rule = $rule;
        $this->catalogRuleRepository = $catalogRuleRepository;
        $this->conditionFactory = $conditionFactory;
    }

    /**
     * @return void
     */
    public function __clone(): void
    {
        $this->rule = clone $this->rule;
    }

    /**
     * @return RuleBuilder
     */
    public static function aCatalogRule(): RuleBuilder
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var RuleInterface $rule */
        $rule = $objectManager->create(RuleInterface::class);

        return new static(
            $rule,
            $objectManager->create(CatalogRuleRepositoryInterface::class),
            $objectManager->create(ConditionFactory::class),
        );
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function withName(string $name): RuleBuilder
    {
        $builder = clone $this;
        $builder->rule->setName($name);

        return $builder;
    }

    /**
     * @param bool $isActive
     *
     * @return $this
     */
    public function withIsActive(bool $isActive): RuleBuilder
    {
        $builder = clone $this;
        $builder->rule->setIsActive($isActive);

        return $builder;
    }

    /**
     * @param bool $stopRulesProcessing
     *
     * @return $this
     */
    public function withStopRulesProcessing(bool $stopRulesProcessing): RuleBuilder
    {
        $builder = clone $this;
        $builder->rule->setStopRulesProcessing($stopRulesProcessing);

        return $builder;
    }

    /**
     * @param int[] $websiteIds
     *
     * @return $this
     */
    public function withWebsiteIds(array $websiteIds): RuleBuilder
    {
        $builder = clone $this;
        $builder->rule->setWebsiteIds(implode(',', $websiteIds));

        return $builder;
    }

    /**
     * @param int[] $customerGroupIds
     *
     * @return $this
     */
    public function withCustomerGroupIds(array $customerGroupIds): RuleBuilder
    {
        $builder = clone $this;
        $builder->rule->setCustomerGroupIds(implode(',', $customerGroupIds));

        return $builder;
    }

    /**
     * @param string $fromDate
     *
     * @return $this
     */
    public function withFromDate(string $fromDate): RuleBuilder
    {
        $builder = clone $this;
        $builder->rule->setFromDate($fromDate);

        return $builder;
    }

    /**
     * @param string $toDate
     *
     * @return $this
     */
    public function withToDate(string $toDate): RuleBuilder
    {
        $builder = clone $this;
        $builder->rule->setToDate($toDate);

        return $builder;
    }

    /**
     * @param float $discountAmount
     *
     * @return $this
     */
    public function withDiscountAmount(float $discountAmount): RuleBuilder
    {
        $builder = clone $this;
        $builder->rule->setDiscountAmount($discountAmount);

        return $builder;
    }

    /**
     * @param int $sortOrder
     *
     * @return $this
     */
    public function withSortOrder(int $sortOrder): RuleBuilder
    {
        $builder = clone $this;
        $builder->rule->setSortOrder($sortOrder);

        return $builder;
    }

    /**
     * e.g. 'by_percent', 'by_fixed'
     *
     * @param string $simpleAction
     *
     * @return $this
     */
    public function withSimpleAction(string $simpleAction): RuleBuilder
    {
        $builder = clone $this;
        $builder->rule->setSimpleAction($simpleAction);

        return $builder;
    }

    /**
     * data format
     * [
     *   [
     *     'attribute' => 'klevu_test_attribute',
     *     'operator' => '==',
     *     'value' => 'test_attribute_value'
     *   ]
     * ]
     *
     * @param mixed[][] $conditions
     * @param string $type
     *
     * @return $this
     */
    public function withConditions(array $conditions, string $type = 'all'): RuleBuilder
    {
        $builder = clone $this;
        $ruleConditions = [];
        foreach ($conditions as $condition) {
            /** @var ConditionInterface $ruleCondition */
            $ruleCondition = $builder->conditionFactory->create();
            $ruleCondition->setType(ProductCondition::class);
            $ruleCondition->setAttribute($condition['attribute']);
            $ruleCondition->setOperator($condition['operator'] ?? '==');
            $ruleCondition->setValue($condition['value']);
            $ruleConditions[] = $ruleCondition;
        }
        /** @var ConditionInterface $combinedCondition */
        $combinedCondition = $builder->conditionFactory->create();
        $combinedCondition->setType(CombineCondition::class);
        $combinedCondition->setAttribute($type);
        $combinedCondition->setValue('1');
        $combinedCondition->setConditions($ruleConditions);

        $builder->rule->setRuleCondition($combinedCondition);

        return $builder;
    }

    /**
     * @return RuleInterface
     * @throws \Exception
     */
    public function build(): RuleInterface
    {
        try {
            $rule = $this->createRule();
        } catch (\Exception $e) {
            $e->getPrevious();
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }

        return $rule;
    }

    /**
     * @return RuleInterface
     * @throws CouldNotSaveException
     */
    private function createRule(): RuleInterface
    {
        $builder = clone $this;

        return $builder->catalogRuleRepository->save($builder->rule);
    }

    /**
     * @param \Throwable|null $exception
     * @return bool
     */
    private static function isTransactionException(?\Throwable $exception): bool
    {
        if ($exception === null) {
            return false;
        }
        return (bool) preg_match(
            '{please retry transaction|DDL statements are not allowed in transactions}i',
            $exception->getMessage(),
        );
    }
}
