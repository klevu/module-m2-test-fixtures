<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Customer\Group;

use Klevu\TestFixtures\Exception\IndexingFailed;
use Magento\Customer\Api\Data\GroupExcludedWebsiteInterface;
use Magento\Customer\Api\Data\GroupExcludedWebsiteInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\StateException;
use Magento\Tax\Model\TaxClass\Source\Customer;
use Magento\Tax\Model\TaxClass\Source\Customer as CustomerTaxClassSource;
use Magento\TestFramework\Helper\Bootstrap;

class CustomerGroupBuilder
{
    /**
     * @var GroupInterface
     */
    private GroupInterface $customerGroup;
    /**
     * @var GroupRepositoryInterface
     */
    private GroupRepositoryInterface $customerGroupRepository;
    /**
     * @var CustomerTaxClassSource
     */
    private CustomerTaxClassSource $customerTaxClassSource;
    /**
     * @var GroupExcludedWebsiteRepositoryInterface
     */
    private GroupExcludedWebsiteRepositoryInterface $groupExcludedWebsiteRepository;
    /**
     * @var GroupExcludedWebsiteInterfaceFactory
     */
    private GroupExcludedWebsiteInterfaceFactory $groupExcludedWebsiteFactory;
    /**
     * @var mixed[]|null
     */
    private ?array $taxClasses = null;
    /**
     * @var int[]
     */
    private array $excludedWebsiteIds = [];

    /**
     * @param GroupInterface $customerGroup
     * @param GroupRepositoryInterface $customerGroupRepository
     * @param Customer $customerTaxClassSource
     * @param GroupExcludedWebsiteRepositoryInterface $groupExcludedWebsiteRepository
     * @param GroupExcludedWebsiteInterfaceFactory $groupExcludedWebsiteFactory
     */
    public function __construct(
        GroupInterface $customerGroup,
        GroupRepositoryInterface $customerGroupRepository,
        CustomerTaxClassSource $customerTaxClassSource,
        GroupExcludedWebsiteRepositoryInterface $groupExcludedWebsiteRepository,
        GroupExcludedWebsiteInterfaceFactory $groupExcludedWebsiteFactory,
    ) {
        $this->customerGroup = $customerGroup;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->customerTaxClassSource = $customerTaxClassSource;
        $this->groupExcludedWebsiteRepository = $groupExcludedWebsiteRepository;
        $this->groupExcludedWebsiteFactory = $groupExcludedWebsiteFactory;
    }

    /**
     * @return CustomerGroupBuilder
     */
    public static function addCustomerGroup(): CustomerGroupBuilder //phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            $objectManager->create(GroupInterface::class),
            $objectManager->create(GroupRepositoryInterface::class),
            $objectManager->create(CustomerTaxClassSource::class),
            $objectManager->create(GroupExcludedWebsiteRepositoryInterface::class),
            $objectManager->create(GroupExcludedWebsiteInterfaceFactory::class),
        );
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    public function withCode(string $code): CustomerGroupBuilder
    {
        $builder = clone $this;
        $builder->customerGroup->setCode($code);

        return $builder;
    }

    /**
     * @param int $taxClassId
     *
     * @return $this
     */
    public function withTaxClassId(int $taxClassId): CustomerGroupBuilder
    {
        $builder = clone $this;
        $builder->customerGroup->setTaxClassId($taxClassId);

        return $builder;
    }

    /**
     * @param int[] $excludedIds
     *
     * @return $this
     */
    public function withExcludedWebsiteIds(array $excludedIds): CustomerGroupBuilder
    {
        $builder = clone $this;
        $builder->excludedWebsiteIds = array_map(
            callback: 'intval',
            array: $excludedIds,
        );

        return $builder;
    }

    /**
     * @return GroupInterface
     * @throws \Exception
     */
    public function build(): GroupInterface
    {
        try {
            $builder = $this->createCustomerGroup();
            $customerGroup = $this->saveCustomerGroup(builder: $builder);
            $this->excludeWebsites(group: $customerGroup);

            return $customerGroup;
        } catch (\Exception $e) {
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexingFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }
    }

    /**
     * @return GroupInterface
     * @throws StateException
     */
    public function buildWithoutSave(): GroupInterface
    {
        $builder = $this->createCustomerGroup();

        return $builder->customerGroup;
    }

    /**
     * @return CustomerGroupBuilder
     * @throws StateException
     */
    private function createCustomerGroup(): CustomerGroupBuilder
    {
        $builder = clone $this;
        if (!$builder->customerGroup->getCode()) {
            $builder->customerGroup->setCode('Klevu Test Customer Group 1');
        }
        if (!$builder->customerGroup->getTaxClassId()) {
            $builder->customerGroup->setTaxClassId(taxClassId: $this->getDefaultTaxClassId());
        }
        $builder->customerGroup->setTaxClassName(
            taxClassName: $this->getTaxClassName(
                taxClassId: $builder->customerGroup->getTaxClassId(),
            ),
        );

        return $builder;
    }

    /**
     * @param CustomerGroupBuilder $builder
     *
     * @return GroupInterface
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws InvalidTransitionException
     */
    private function saveCustomerGroup(CustomerGroupBuilder $builder): GroupInterface
    {
        return $this->customerGroupRepository->save(group: $builder->customerGroup);
    }

    /**
     * @param GroupInterface $group
     *
     * @return void
     * @throws LocalizedException
     */
    private function excludeWebsites(GroupInterface $group): void
    {
        $builder = clone $this;
        foreach ($builder->excludedWebsiteIds as $websiteId) {
            /** @var GroupExcludedWebsiteInterface $groupExcludedWebsite */
            $groupExcludedWebsite = $this->groupExcludedWebsiteFactory->create();
            $groupExcludedWebsite->setGroupId(id: (int)$group->getId());
            $groupExcludedWebsite->setExcludedWebsiteId(websiteId: $websiteId);
            $this->groupExcludedWebsiteRepository->save(groupExcludedWebsite: $groupExcludedWebsite);
        }
    }

    /**
     * Get the "Retail Customer" tax class id if it exists, else return the tax class with the lowest ID
     *
     * @return int
     * @throws StateException
     */
    private function getDefaultTaxClassId(): int
    {
        $taxClasses = $this->getAllTaxClasses();
        $retailCustomerTaxClasses = array_filter(
            array: $taxClasses,
            callback: static fn (array $taxClass): bool => $taxClass['label'] === 'Retail Customer',
        );
        if ($retailCustomerTaxClasses) {
            $retailCustomerTaxClass = array_shift($retailCustomerTaxClasses);
            if ($retailCustomerTaxClass['value'] ?? null) {
                return (int)$retailCustomerTaxClass['value'];
            }
        }
        $taxClassIds = array_map(
            callback: static fn (array $taxClass): int => (int)$taxClass['value'],
            array: $taxClasses,
        );

        return array_shift($taxClassIds);
    }

    /**
     * @param int $taxClassId
     *
     * @return string
     * @throws StateException
     */
    private function getTaxClassName(int $taxClassId): string
    {
        $taxClasses = $this->getAllTaxClasses();

        $taxClassNames = array_filter(
            array: $taxClasses,
            callback: static fn (array $taxClass): bool => (int)$taxClass['value'] === $taxClassId,
        );

        return $taxClassNames['label'] ?? '';
    }

    /**
     * @return mixed[]
     * @throws StateException
     */
    private function getAllTaxClasses(): array
    {
        if (null === $this->taxClasses) {
            $this->taxClasses = $this->customerTaxClassSource->getAllOptions();
        }

        return $this->taxClasses;
    }

    /**
     * @param \Throwable|null $exception
     *
     * @return bool
     *
     */
    private static function isTransactionException( // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction
        ?\Throwable $exception,
    ): bool {
        if ($exception === null) {
            return false;
        }

        return (bool)preg_match(
            '{please retry transaction|DDL statements are not allowed in transactions}i',
            $exception->getMessage(),
        );
    }
}
