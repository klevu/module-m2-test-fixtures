<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Tax;

use Magento\Tax\Api\Data\TaxClassInterface;

class TaxClassFixture
{
    /**
     * @var TaxClassInterface
     */
    private TaxClassInterface $taxClass;

    /**
     * @param TaxClassInterface $taxClass
     */
    public function __construct(TaxClassInterface $taxClass)
    {
        $this->taxClass = $taxClass;
    }

    /**
     * @return TaxClassInterface
     */
    public function getTaxClass(): TaxClassInterface
    {
        return $this->taxClass;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->taxClass->getId();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        TaxClassFixtureRollback::create()->execute($this);
    }
}
