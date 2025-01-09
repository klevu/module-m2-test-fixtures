<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Tax;

use Magento\Tax\Api\Data\TaxRateInterface;

class TaxRateFixture
{
    /**
     * @var TaxRateInterface
     */
    private TaxRateInterface $taxRate;

    /**
     * @param TaxRateInterface $taxRate
     */
    public function __construct(TaxRateInterface $taxRate)
    {
        $this->taxRate = $taxRate;
    }

    /**
     * @return TaxRateInterface
     */
    public function getTaxRate(): TaxRateInterface
    {
        return $this->taxRate;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->taxRate->getId();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        TaxRateFixtureRollback::create()->execute($this);
    }
}
