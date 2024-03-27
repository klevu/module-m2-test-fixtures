<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Website;

use Klevu\TestFixtures\Exception\FixturePoolMissingException;

trait WebsiteTrait
{
    /**
     * @var WebsiteFixturesPool|null
     */
    private ?WebsiteFixturesPool $websiteFixturesPool = null;

    /**
     * @param mixed[]|null $websiteData
     *
     * @return void
     * @throws FixturePoolMissingException
     * @throws \Exception
     */
    public function createWebsite(?array $websiteData = []): void
    {
        if (null === $this->websiteFixturesPool) {
            throw new FixturePoolMissingException(
                'websiteFixturesPool has not been created in your test setUp method.',
            );
        }
        $this->removeExistingWebsiteWithSameCode($websiteData);

        $websiteBuilder = WebsiteBuilder::addWebsite();
        if (!empty($websiteData['code'])) {
            $websiteBuilder->withCode($websiteData['code']);
        }
        if (!empty($websiteData['name'])) {
            $websiteBuilder->withName($websiteData['name']);
        }
        if (!empty($websiteData['default_group_id'])) {
            $websiteBuilder->withDefaultGroupId($websiteData['default_group_id']);
        }
        $this->websiteFixturesPool->add(
            $websiteBuilder->build(),
            $websiteData['key'] ?? 'test_website',
        );
    }

    /**
     * @param mixed[] $websiteData
     *
     * @return void
     * @throws \Exception
     */
    private function removeExistingWebsiteWithSameCode(array $websiteData): void
    {
        try {
            $websiteFixture = $this->websiteFixturesPool->get($websiteData['code'] ?? 'klevu_test_website_1');
            $websiteFixture->rollback();
        } catch (\OutOfBoundsException) {
            // this is fine website with code could not be loaded
        }
    }
}
