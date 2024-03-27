<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Cms;

use Klevu\TestFixtures\Exception\FixturePoolMissingException;
use Magento\Framework\Exception\LocalizedException;

trait PageTrait
{
    /**
     * @var PageFixturesPool|null
     */
    private ?PageFixturesPool $pageFixturesPool = null;

    /**
     * @param mixed[]|null $pageData
     *
     * @return void
     * @throws FixturePoolMissingException
     * @throws LocalizedException
     */
    private function createPage(?array $pageData = []): void
    {
        if (null === $this->pageFixturesPool) {
            throw new FixturePoolMissingException(
                'pageFixturesPool has not been created in your test setUp method.',
            );
        }

        $pageBuilder = PageBuilder::addPage();
        if (!empty($pageData['identifier'])) {
            $pageBuilder->withIdentifier($pageData['identifier']);
        }
        if (!empty($pageData['title'])) {
            $pageBuilder->withTitle($pageData['title']);
        }
        if (isset($pageData['is_active'])) {
            $pageBuilder->withIsActive($pageData['is_active']);
        }
        if (isset($pageData['store_id'])) {
            $pageBuilder->withStoreId($pageData['store_id']);
        }
        if (isset($pageData['stores'])) {
            $pageBuilder->withStores($pageData['stores']);
        }

        $this->pageFixturesPool->add(
            $pageBuilder->build(),
            $pageData['key'] ?? 'test_page',
        );
    }
}
