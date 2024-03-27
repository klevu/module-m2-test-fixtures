<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Website;

use Magento\Store\Api\Data\WebsiteInterface;

class WebsiteFixturesPool
{
    /**
     * @var WebsiteFixture[]
     */
    private array $websiteFixtures = [];

    /**
     * @param WebsiteInterface $website
     * @param string|null $key
     *
     * @return void
     */
    public function add(WebsiteInterface $website, ?string $key = null): void
    {
        if ($key === null) {
            $this->websiteFixtures[] = new WebsiteFixture($website);
        } else {
            $this->websiteFixtures[$key] = new WebsiteFixture($website);
        }
    }

    /**
     * Returns website fixture by key, or last added if key not specified
     *
     * @param string|null $key
     *
     * @return WebsiteFixture
     */
    public function get(?string $key = null): WebsiteFixture
    {
        if ($key === null) {
            $key = array_key_last($this->websiteFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->websiteFixtures)) {
            throw new \OutOfBoundsException('No matching website found in fixture pool');
        }

        return $this->websiteFixtures[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        WebsiteFixtureRollback::create()->execute(...array_values($this->websiteFixtures));
        $this->websiteFixtures = [];
    }
}
