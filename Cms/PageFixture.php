<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Cms;

use Magento\Cms\Api\Data\PageInterface;

class PageFixture
{
    /**
     * @var PageInterface
     */
    private PageInterface $page;

    /**
     * @param PageInterface $page
     */
    public function __construct(PageInterface $page)
    {
        $this->page = $page;
    }

    /**
     * @return PageInterface
     */
    public function getPage(): PageInterface
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->page->getId();
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->page->getIdentifier();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        PageFixtureRollback::create()->execute($this);
    }
}
