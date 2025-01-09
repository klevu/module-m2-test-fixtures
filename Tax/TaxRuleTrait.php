<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Tax;

use Klevu\TestFixtures\Exception\FixturePoolMissingException;

trait TaxRuleTrait
{
    /**
     * @var TaxRuleFixturePool|null
     */
    private ?TaxRuleFixturePool $taxRuleFixturePool = null;

    /**
     * @param mixed[]|null $taxRuleData
     *
     * @return void
     * @throws \Exception
     */
    public function createTaxRule(?array $taxRuleData = []): void
    {
        if (null === $this->taxRuleFixturePool) {
            throw new FixturePoolMissingException(
                message: 'taxRuleFixturePool has not been created in your test setUp method.',
            );
        }
        $taxRuleBuilder = TaxRuleBuilder::addTaxRule();
        $taxRuleBuilder->withTaxRateIds(taxRateIds: $taxRuleData['tax_rate_ids']);
        if ($taxRuleData['code'] ?? null) {
            $taxRuleBuilder->withCode(code: $taxRuleData['code']);
        }
        if ($taxRuleData['customer_tax_class_ids'] ?? null) {
            $taxRuleBuilder->withCustomerTaxClassIds(customerTaxClassIds: $taxRuleData['customer_tax_class_ids']);
        }
        if ($taxRuleData['product_tax_class_ids'] ?? null) {
            $taxRuleBuilder->withProductTaxClassIds(productTaxClassIds: $taxRuleData['product_tax_class_ids']);
        }
        if (null !== ($taxRuleData['priority'] ?? null)) {
            $taxRuleBuilder->withPriority(priority: $taxRuleData['priority']);
        }
        if (null !== ($taxRuleData['calculate_subtotal'] ?? null)) {
            $taxRuleBuilder->withCalculateSubtotal(calculateSubtotal: $taxRuleData['calculate_subtotal']);
        }
        $this->taxRuleFixturePool->add(
            taxRule: $taxRuleBuilder->build(),
            key: $taxRuleData['key'] ?? 'test_tax_rule',
        );
    }
}
