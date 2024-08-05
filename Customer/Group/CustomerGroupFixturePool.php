<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Customer\Group;

use Magento\Customer\Api\Data\GroupInterface;

class CustomerGroupFixturePool
{
    /**
     * @var CustomerGroupFixture[]
     */
    private array $customerGroupFixtures = [];

    /**
     * @param GroupInterface $customerGroup
     * @param string|null $key
     *
     * @return void
     */
    public function add(GroupInterface $customerGroup, ?string $key = null): void
    {
        if ($key === null) {
            $this->customerGroupFixtures[] = new CustomerGroupFixture($customerGroup);
        } else {
            $this->customerGroupFixtures[$key] = new CustomerGroupFixture($customerGroup);
        }
    }

    /**
     * Returns customer group fixture by key, or last added if key not specified
     *
     * @param string|null $key
     *
     * @return CustomerGroupFixture
     */
    public function get(?string $key = null): CustomerGroupFixture
    {
        if ($key === null) {
            $key = array_key_last($this->customerGroupFixtures);
        }
        if ($key === null || !array_key_exists($key, $this->customerGroupFixtures)) {
            throw new \OutOfBoundsException('No matching customer group found in fixture pool');
        }

        return $this->customerGroupFixtures[$key];
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function rollback(): void
    {
        CustomerGroupFixtureRollback::create()->execute(...array_values($this->customerGroupFixtures));
        $this->customerGroupFixtures = [];
    }
}
