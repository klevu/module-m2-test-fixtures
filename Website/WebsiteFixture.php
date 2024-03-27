<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Website;

use Magento\Store\Api\Data\WebsiteInterface;

class WebsiteFixture
{
    /**
     * @var WebsiteInterface
     */
    private WebsiteInterface $website;

    /**
     * @param WebsiteInterface $website
     */
    public function __construct(WebsiteInterface $website)
    {
        $this->website = $website;
    }

    /**
     * @return WebsiteInterface
     */
    public function get(): WebsiteInterface
    {
        return $this->website;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->website->getId();
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->website->getCode();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->website->getName();
    }

    /**
     * @return int
     */
    public function getDefaultGroupId(): int
    {
        return (int)$this->website->getDefaultGroupId();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        WebsiteFixtureRollback::create()->execute($this);
    }
}
