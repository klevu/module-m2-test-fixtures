<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Store;

use Klevu\TestFixtures\Exception\FixturePoolMissingException;

trait StoreTrait
{
    /**
     * @var StoreFixturesPool|null
     */
    private ?StoreFixturesPool $storeFixturesPool = null;

    /**
     * @param mixed[]|null $storeData
     *
     * @return void
     * @throws FixturePoolMissingException
     * @throws \Exception
     */
    private function createStore(?array $storeData = []): void
    {
        if (null === $this->storeFixturesPool) {
            throw new FixturePoolMissingException(
                'storeFixturesPool has not been created in your test setUp method.',
            );
        }
        $storeBuilder = StoreBuilder::addStore();
        if (!empty($storeData['code'])) {
            $storeBuilder = $storeBuilder->withCode($storeData['code']);
        }
        if (!empty($storeData['name'])) {
            $storeBuilder = $storeBuilder->withName($storeData['name']);
        }
        if (isset($storeData['website_id'])) {
            $storeBuilder = $storeBuilder->withWebsiteId($storeData['website_id']);
        }
        if (isset($storeData['group_id'])) {
            $storeBuilder = $storeBuilder->withGroupId($storeData['group_id']);
        }
        if (isset($storeData['is_active'])) {
            $storeBuilder = $storeBuilder->withIsActive($storeData['is_active']);
        }
        if (isset($storeData['with_sequence'])) {
            $storeBuilder->withSequence($storeData['with_sequence']);
        }

        $this->storeFixturesPool->add(
            $storeBuilder->build(),
            $storeData['key'] ?? 'test_store',
        );
    }
}
