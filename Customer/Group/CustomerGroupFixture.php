<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Customer\Group;

use Magento\Customer\Api\Data\GroupInterface;

class CustomerGroupFixture
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
    public function getCustomerGroup(): GroupInterface
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
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        CustomerGroupFixtureRollback::create()->execute($this);
    }
}
