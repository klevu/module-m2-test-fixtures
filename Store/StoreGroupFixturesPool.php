<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Store;

use Magento\Store\Api\Data\GroupInterface;

class StoreGroupFixturesPool
{
    /**
     * @var StoreGroupFixture[]
     */
    private array $storeGroupFixtures = [];

    /**
     * @param GroupInterface $storeGroup
     * @param string|null $key
     *
     * @return void
     */
    public function add(GroupInterface $storeGroup, ?string $key = null): void
    {
        if ($key === null) {
            $this->storeGroupFixtures[] = new StoreGroupFixture($storeGroup);
        } else {
            $this->storeGroupFixtures[$key] = new StoreGroupFixture($storeGroup);
        }
    }

    /**
     * Returns store group fixture by key, or last added if key not specified
     *
     * @param string|null $key
     *
     * @return StoreGroupFixture
     */
    public function get(?string $key = null): StoreGroupFixture
    {
        if ($key === null) {
            $key = array_key_last($this->storeGroupFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->storeGroupFixtures)) {
            throw new \OutOfBoundsException('No matching store group found in fixture pool');
        }

        return $this->storeGroupFixtures[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        StoreGroupFixtureRollback::create()->execute(...array_values($this->storeGroupFixtures));
        $this->storeGroupFixtures = [];
    }
}
