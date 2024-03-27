<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog;

use Klevu\TestFixtures\Catalog\Rule\RuleBuilder;
use Klevu\TestFixtures\Catalog\Rule\RuleFixturePool;
use Magento\Customer\Model\Group;

trait RuleTrait
{
    /**
     * @var RuleFixturePool
     */
    private mixed $ruleFixturePool = null;

    /**
     * @throws \Exception
     */
    public function createRule(?array $ruleData = []): void
    {
        $ruleBuilder = RuleBuilder::aCatalogRule();

        $ruleBuilder = $ruleBuilder->withName(
            name: $ruleData['name'] ?? 'Klevu Test Catalog Rule',
        );

        $ruleBuilder = $ruleBuilder->withIsActive(
            isActive: $ruleData['is_active'] ?? true,
        );

        $ruleBuilder = $ruleBuilder->withStopRulesProcessing(
            stopRulesProcessing: $ruleData['stop_rules'] ?? true,
        );

        $ruleBuilder = $ruleBuilder->withWebsiteIds(
            websiteIds: $ruleData['website_ids'] ?? [1],
        );

        $ruleBuilder = $ruleBuilder->withCustomerGroupIds(
            customerGroupIds: $ruleData['customer_group_ids'] ?? [Group::NOT_LOGGED_IN_ID],
        );

        $ruleBuilder = $ruleBuilder->withFromDate(
            fromDate: $ruleData['from_date'] ?? date(format: 'y-m-d h:i:s', timestamp: time() - (3600 * 24)),
        );

        $ruleBuilder = $ruleBuilder->withToDate(
            toDate: $ruleData['to_date'] ?? date(format: 'y-m-d h:i:s', timestamp: time() + (3600 * 24)),
        );

        $ruleBuilder = $ruleBuilder->withDiscountAmount(
            discountAmount: $ruleData['discount_amount'] ?? 10.00,
        );

        $ruleBuilder = $ruleBuilder->withSimpleAction(
            simpleAction: ($ruleData['is_percent'] ?? true)
                ? 'by_percent'
                : 'by_fixed',
        );

        $ruleBuilder = $ruleBuilder->withSortOrder(
            sortOrder: $ruleData['sort_order'] ?? 1,
        );

        if ($ruleData['conditions'] ?? null) {
            $ruleBuilder = $ruleBuilder->withConditions(
                conditions: $ruleData['conditions'],
                type: $ruleData['condition_type'] ?? 'all',
            );
        }

        $this->ruleFixturePool->add(
            rule: $ruleBuilder->build(),
            key: $ruleData['key'] ?? 'test_rule',
        );
    }
}
