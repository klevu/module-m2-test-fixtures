<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog;

use Klevu\TestFixtures\Catalog\Category\CategoryBuilder;
use TddWizard\Fixtures\Catalog\CategoryFixturePool;

trait CategoryTrait
{
    /**
     * @var CategoryFixturePool|null
     */
    private ?CategoryFixturePool $categoryFixturePool = null;

    /**
     * Example usage setting store level data
     * $this->createCategory(
     *   categoryData: [
     *     'name' => 'GLOBAL NAME',
     *     'stores' => [
     *        $store1->getId() => [
     *          'name' => 'NAME IN STORE 1',
     *        ],
     *        $store2->getId() => [
     *          'name' => 'NAME IN STORE 2',
     *        ],
     *      ],
     *   ],
     * );
     *
     * @param mixed[] $categoryData
     *
     * @return void
     * @throws \Exception
     */
    public function createCategory(?array $categoryData = []): void
    {
        if ($categoryData['root'] ?? null) {
            $categoryBuilder = CategoryBuilder::rootCategory();
        } else {
            $categoryBuilder = ($categoryData['parent'] ?? null)
                ? CategoryBuilder::childCategoryOf($categoryData['parent'])
                : CategoryBuilder::topLevelCategory($categoryData['root_id'] ?? null);
        }

        if (!empty($categoryData['name'])) {
            $categoryBuilder = $categoryBuilder->withName($categoryData['name']);
        }
        if (!empty($categoryData['description'])) {
            $categoryBuilder = $categoryBuilder->withDescription($categoryData['description']);
        }
        if (!empty($categoryData['url_key'])) {
            $categoryBuilder = $categoryBuilder->withUrlKey($categoryData['url_key']);
        }
        if (isset($categoryData['is_active'])) {
            $categoryBuilder = $categoryBuilder->withIsActive($categoryData['is_active']);
        }
        if (isset($categoryData['is_anchor'])) {
            $categoryBuilder = $categoryBuilder->withIsAnchor($categoryData['is_anchor']);
        }
        if (isset($categoryData['display_mode'])) {
            $categoryBuilder = $categoryBuilder->withDisplayMode($categoryData['display_mode']);
        }
        if (!empty($categoryData['products'])) {
            $categoryBuilder = $categoryBuilder->withProducts($categoryData['products']);
        }
        if (!empty($categoryData['custom_attributes'])) {
            $categoryBuilder = $categoryBuilder->withCustomAttributes($categoryData['custom_attributes']);
        }
        if (!empty($categoryData['image'])) {
            $categoryBuilder = $categoryBuilder->withImage($categoryData['image']);
        }
        if (isset($categoryData['store_id'])) {
            $categoryBuilder = $categoryBuilder->withStoreId($categoryData['store_id']);
        }
        if (!empty($categoryData['stores'])) {
            foreach ($categoryData['stores'] as $storeIdKey => $categoryStoreData) {
                if (!empty($categoryData['name'])) {
                    $categoryBuilder = $categoryBuilder->withName(
                        $categoryStoreData['name'],
                        $storeIdKey,
                    );
                }
                if (!empty($categoryStoreData['description'])) {
                    $categoryBuilder = $categoryBuilder->withDescription(
                        $categoryStoreData['description'],
                        $storeIdKey,
                    );
                }
                if (!empty($categoryStoreData['url_key'])) {
                    $categoryBuilder = $categoryBuilder->withUrlKey(
                        $categoryStoreData['url_key'],
                        $storeIdKey,
                    );
                }
                if (isset($categoryData['is_active'])) {
                    $categoryBuilder = $categoryBuilder->withIsActive(
                        $categoryStoreData['is_active'],
                        $storeIdKey,
                    );
                }
                if (!empty($categoryStoreData['custom_attributes'])) {
                    $categoryBuilder = $categoryBuilder->withCustomAttributes(
                        $categoryStoreData['custom_attributes'],
                        $storeIdKey,
                    );
                }
            }
        }

        $this->categoryFixturePool->add(
            $categoryBuilder->build(),
            $categoryData['key'] ?? 'test_category',
        );
    }
}
