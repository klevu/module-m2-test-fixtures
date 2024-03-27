<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Store;

use Magento\Store\Api\Data\GroupInterface;

class StoreGroupFixture
{
    /**
     * @var GroupInterface
     */
    private GroupInterface $group;

    /**
     * @param GroupInterface $group
     */
    public function __construct(GroupInterface $group)
    {
        $this->group = $group;
    }

    /**
     * @return GroupInterface
     */
    public function get(): GroupInterface
    {
        return $this->group;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->group->getId();
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->group->getCode();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->group->getName();
    }

    /**
     * @return int
     */
    public function getWebsiteId(): int
    {
        return (int)$this->group->getWebsiteId();
    }

    /**
     * @return bool
     */
    public function getRootCategoryId(): bool
    {
        return (bool)$this->group->getRootCategoryId();
    }

    /**
     * @return int
     */
    public function getDefaultStoreId(): int
    {
        return (int)$this->group->getDefaultStoreId();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        StoreGroupFixtureRollback::create()->execute($this);
    }
}
