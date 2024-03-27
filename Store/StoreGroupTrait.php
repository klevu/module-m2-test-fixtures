<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Store;

use Klevu\TestFixtures\Exception\FixturePoolMissingException;

trait StoreGroupTrait
{
    /**
     * @var StoreGroupFixturesPool|null
     */
    private ?StoreGroupFixturesPool $storeGroupFixturesPool = null;

    /**
     * @param mixed[]|null $storeData
     *
     * @return void
     * @throws FixturePoolMissingException
     * @throws \Exception
     */
    private function createStoreGroup(?array $storeData = []): void
    {
        if (null === $this->storeGroupFixturesPool) {
            throw new FixturePoolMissingException(
                'storeGroupFixturesPool has not been created in your test setUp method.',
            );
        }
        $storeGroupBuilder = StoreGroupBuilder::addStoreGroup();
        if (!empty($storeData['code'])) {
            $storeGroupBuilder = $storeGroupBuilder->withCode($storeData['code']);
        }
        if (!empty($storeData['name'])) {
            $storeGroupBuilder = $storeGroupBuilder->withName($storeData['name']);
        }
        if (isset($storeData['website_id'])) {
            $storeGroupBuilder = $storeGroupBuilder->withWebsiteId($storeData['website_id']);
        }
        if (isset($storeData['root_category_id'])) {
            $storeGroupBuilder = $storeGroupBuilder->withRootCategoryId($storeData['root_category_id']);
        }

        $this->storeGroupFixturesPool->add(
            $storeGroupBuilder->build(),
            $storeData['key'] ?? 'test_store_group',
        );
    }
}
