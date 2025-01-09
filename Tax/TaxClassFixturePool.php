<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Tax;

use Magento\Tax\Api\Data\TaxClassInterface;

class TaxClassFixturePool
{
    /**
     * @var TaxClassFixture[]
     */
    private array $taxClassFixtures = [];

    /**
     * @param TaxClassInterface $taxClass
     * @param string|null $key
     *
     * @return void
     */
    public function add(TaxClassInterface $taxClass, ?string $key = null): void
    {
        if ($key === null) {
            $this->taxClassFixtures[] = new TaxClassFixture(taxClass: $taxClass);
        } else {
            $this->taxClassFixtures[$key] = new TaxClassFixture(taxClass: $taxClass);
        }
    }

    /**
     * Returns tax class fixture by key, or last added if key not specified
     *
     * @param string|null $key
     *
     * @return TaxClassFixture
     */
    public function get(?string $key = null): TaxClassFixture
    {
        if ($key === null) {
            $key = array_key_last($this->taxClassFixtures);
        }
        if ($key === null || !array_key_exists(key: $key, array: $this->taxClassFixtures)) {
            throw new \OutOfBoundsException(message: 'No matching tax class found in fixture pool');
        }

        return $this->taxClassFixtures[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        TaxClassFixtureRollback::create()->execute(...array_values($this->taxClassFixtures));
        $this->taxClassFixtures = [];
    }
}
