<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Store;

use Magento\Store\Api\Data\StoreInterface;

class StoreFixturesPool
{
    /**
     * @var StoreFixture[]
     */
    private array $storeFixtures = [];

    /**
     * @param StoreInterface $store
     * @param string|null $key
     *
     * @return void
     */
    public function add(StoreInterface $store, ?string $key = null): void
    {
        if ($key === null) {
            $this->storeFixtures[] = new StoreFixture($store);
        } else {
            $this->storeFixtures[$key] = new StoreFixture($store);
        }
    }

    /**
     * Returns store fixture by key, or last added if key not specified
     *
     * @param string|null $key
     *
     * @return StoreFixture|StoreInterface
     */
    public function get(?string $key = null): StoreFixture
    {
        if ($key === null) {
            $key = array_key_last($this->storeFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->storeFixtures)) {
            throw new \OutOfBoundsException('No matching store found in fixture pool');
        }

        return $this->storeFixtures[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        StoreFixtureRollback::create()->execute(...array_values($this->storeFixtures));
        $this->storeFixtures = [];
    }
}
