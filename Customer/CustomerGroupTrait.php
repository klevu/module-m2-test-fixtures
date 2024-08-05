<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Customer;

use Klevu\TestFixtures\Customer\Group\CustomerGroupBuilder;
use Klevu\TestFixtures\Customer\Group\CustomerGroupFixturePool;
use Klevu\TestFixtures\Exception\FixturePoolMissingException;

trait CustomerGroupTrait
{
    /**
     * @var CustomerGroupFixturePool|null
     */
    private ?CustomerGroupFixturePool $customerGroupFixturePool = null;

    /**
     * @param mixed[]|null $customerGroupData
     *
     * @return void
     * @throws \Exception
     */
    public function createCustomerGroup(?array $customerGroupData = []): void
    {
        if (null === $this->customerGroupFixturePool) {
            throw new FixturePoolMissingException(
                'customerGroupFixturePool has not been created in your test setUp method.',
            );
        }
        $customerGroupBuilder = CustomerGroupBuilder::addCustomerGroup();
        if (!empty($customerGroupData['code'])) {
            $customerGroupBuilder = $customerGroupBuilder->withCode(code: $customerGroupData['code']);
        }
        if (!empty($customerGroupData['tax_class_id'])) {
            $customerGroupBuilder = $customerGroupBuilder->withTaxClassId(
                taxClassId: $customerGroupData['tax_class_id'],
            );
        }
        if (!empty($customerGroupData['excluded_website_ids'])) {
            $customerGroupBuilder = $customerGroupBuilder->withExcludedWebsiteIds(
                excludedIds: $customerGroupData['excluded_website_ids'],
            );
        }

        $this->customerGroupFixturePool->add(
            customerGroup: $customerGroupBuilder->build(),
            key: $customerGroupData['key'] ?? 'test_customer_group',
        );
    }
}
