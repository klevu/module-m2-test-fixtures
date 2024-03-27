<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Customer;

use Klevu\TestFixtures\Exception\FixturePoolMissingException;
use Magento\Framework\Exception\LocalizedException;
use TddWizard\Fixtures\Customer\CustomerBuilder;
use TddWizard\Fixtures\Customer\CustomerFixturePool;

trait CustomerTrait
{
    /**
     * @var CustomerFixturePool|null
     */
    private ?CustomerFixturePool $customerFixturePool = null;

    /**
     * @param mixed|null $customerData
     *
     * @return void
     * @throws LocalizedException
     */
    private function createCustomer(?array $customerData = []): void
    {
        if (null === $this->customerFixturePool) {
            throw new FixturePoolMissingException(
                'customerFixturePool has not been created in your test setUp method.',
            );
        }
        $customerBuilder = CustomerBuilder::aCustomer();
        if (!empty($customerData['email'])) {
            $customerBuilder = $customerBuilder->withEmail($customerData['email']);
        }
        if (!empty($customerData['addresses'])) {
            // @see \TddWizard\Fixtures\Customer\AddressBuilder
            $customerBuilder = $customerBuilder->withAddresses($customerData['addresses']);
        }
        if (!empty($customerData['group_id'])) {
            $customerBuilder = $customerBuilder->withGroupId($customerData['group_id']);
        }
        if (!empty($customerData['store_id'])) {
            $customerBuilder = $customerBuilder->withStoreId($customerData['store_id']);
        }
        if (!empty($customerData['website_id'])) {
            $customerBuilder = $customerBuilder->withStoreId($customerData['website_id']);
        }
        if (!empty($customerData['first_name'])) {
            $customerBuilder = $customerBuilder->withFirstname($customerData['first_name']);
        }
        if (!empty($customerData['middle_name'])) {
            $customerBuilder = $customerBuilder->withMiddlename($customerData['middle_name']);
        }
        if (!empty($customerData['last_name'])) {
            $customerBuilder = $customerBuilder->withLastname($customerData['last_name']);
        }
        if (!empty($customerData['prefix'])) {
            $customerBuilder = $customerBuilder->withPrefix($customerData['prefix']);
        }
        if (!empty($customerData['suffix'])) {
            $customerBuilder = $customerBuilder->withSuffix($customerData['suffix']);
        }
        if (!empty($customerData['dob'])) {
            $customerBuilder = $customerBuilder->withDob($customerData['dob']);
        }
        if (!empty($customerData['custom_attributes'])) {
            $customerBuilder = $customerBuilder->withCustomAttributes($customerData['custom_attributes']);
        }
        if (!empty($customerData['confirmation'])) {
            $customerBuilder = $customerBuilder->withConfirmation($customerData['confirmation']);
        }
        if (!empty($customerData['tax_vat'])) {
            $customerBuilder = $customerBuilder->withTaxvat($customerData['tax_vat']);
        }

        $this->customerFixturePool->add(
            customer: $customerBuilder->build(),
            key: $customerData['key'] ?? 'test_customer',
        );
    }
}
