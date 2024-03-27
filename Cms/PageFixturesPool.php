<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Cms;

use Magento\Cms\Api\Data\PageInterface;

class PageFixturesPool
{
    /**
     * @var PageFixture[]
     */
    private array $pageFixtures = [];

    /**
     * @param PageInterface $page
     * @param string|null $key
     *
     * @return void
     */
    public function add(PageInterface $page, ?string $key = null): void
    {
        if ($key === null) {
            $this->pageFixtures[] = new PageFixture($page);
        } else {
            $this->pageFixtures[$key] = new PageFixture($page);
        }
    }

    /**
     * Returns page fixture by key, or last added if key not specified
     *
     * @param string|null $key
     *
     * @return PageFixture
     */
    public function get(?string $key = null): PageFixture
    {
        if ($key === null) {
            $key = array_key_last($this->pageFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->pageFixtures)) {
            throw new \OutOfBoundsException('No matching page found in fixture pool');
        }

        return $this->pageFixtures[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        PageFixtureRollback::create()->execute(...array_values($this->pageFixtures));
        $this->pageFixtures = [];
    }
}
