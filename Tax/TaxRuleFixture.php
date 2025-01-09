<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Tax;

use Magento\Tax\Api\Data\TaxRuleInterface;

class TaxRuleFixture
{
    /**
     * @var TaxRuleInterface
     */
    private TaxRuleInterface $taxRule;

    /**
     * @param TaxRuleInterface $taxRule
     */
    public function __construct(TaxRuleInterface $taxRule)
    {
        $this->taxRule = $taxRule;
    }

    /**
     * @return TaxRuleInterface
     */
    public function getTaxRule(): TaxRuleInterface
    {
        return $this->taxRule;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->taxRule->getId();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        TaxRuleFixtureRollback::create()->execute($this);
    }
}
