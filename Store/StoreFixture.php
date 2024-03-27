<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Store;

use Magento\Store\Api\Data\StoreInterface;

class StoreFixture
{
    /**
     * @var StoreInterface
     */
    private StoreInterface $store;

    /**
     * @param StoreInterface $store
     */
    public function __construct(StoreInterface $store)
    {
        $this->store = $store;
    }

    /**
     * @return StoreInterface
     */
    public function get(): StoreInterface
    {
        return $this->store;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->store->getId();
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->store->getCode();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->store->getName();
    }

    /**
     * @return int
     */
    public function getWebsiteId(): int
    {
        return (int)$this->store->getWebsiteId();
    }

    /**
     * @return int
     */
    public function getStoreGroupId(): int
    {
        return (int)$this->store->getStoreGroupId();
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return (bool)$this->store->getIsActive();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        StoreFixtureRollback::create()->execute($this);
    }
}
