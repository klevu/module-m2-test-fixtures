<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Tax;

use Magento\Tax\Api\Data\TaxRuleInterface;

class TaxRuleFixturePool
{
    /**
     * @var TaxRuleFixture[]
     */
    private array $taxRuleFixtures = [];

    /**
     * @param TaxRuleInterface $taxRule
     * @param string|null $key
     *
     * @return void
     */
    public function add(TaxRuleInterface $taxRule, ?string $key = null): void
    {
        if ($key === null) {
            $this->taxRuleFixtures[] = new TaxRuleFixture(taxRule: $taxRule);
        } else {
            $this->taxRuleFixtures[$key] = new TaxRuleFixture(taxRule: $taxRule);
        }
    }

    /**
     * Returns tax rule fixture by key, or last added if key not specified
     *
     * @param string|null $key
     *
     * @return TaxRuleFixture
     */
    public function get(?string $key = null): TaxRuleFixture
    {
        if ($key === null) {
            $key = array_key_last($this->taxRuleFixtures);
        }
        if ($key === null || !array_key_exists(key: $key, array: $this->taxRuleFixtures)) {
            throw new \OutOfBoundsException(message: 'No matching tax rule found in fixture pool');
        }

        return $this->taxRuleFixtures[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        TaxRuleFixtureRollback::create()->execute(...array_values($this->taxRuleFixtures));
        $this->taxRuleFixtures = [];
    }
}
