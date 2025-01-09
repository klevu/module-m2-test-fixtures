<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Tax;

use Klevu\TestFixtures\Exception\IndexingFailed;
use Magento\Tax\Api\Data\TaxRuleInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Calculation as TaxCalculation;
use Magento\TestFramework\Helper\Bootstrap;

class TaxRuleBuilder
{
    /**
     * @var TaxRuleInterface
     */
    private TaxRuleInterface $taxRule;
    /**
     * @var TaxRuleRepositoryInterface
     */
    private TaxRuleRepositoryInterface $taxRuleRepository;
    /**
     * @var TaxCalculation
     */
    private TaxCalculation $taxCalculation;
    /**
     * @var TaxHelper
     */
    private TaxHelper $taxHelper;

    /**
     * @param TaxRuleInterface $taxRule
     * @param TaxRuleRepositoryInterface $taxRuleRepository
     * @param TaxCalculation $taxCalculation
     * @param TaxHelper $taxHelper
     */
    public function __construct(
        TaxRuleInterface $taxRule,
        TaxRuleRepositoryInterface $taxRuleRepository,
        TaxCalculation $taxCalculation,
        TaxHelper $taxHelper,
    ) {
        $this->taxRule = $taxRule;
        $this->taxRuleRepository = $taxRuleRepository;
        $this->taxCalculation = $taxCalculation;
        $this->taxHelper = $taxHelper;
    }

    /**
     * @return self
     */
    public static function addTaxRule(): TaxRuleBuilder
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            taxRule: $objectManager->create(TaxRuleInterface::class),
            taxRuleRepository: $objectManager->create(TaxRuleRepositoryInterface::class),
            taxCalculation: $objectManager->create(TaxCalculation::class),
            taxHelper: $objectManager->create(TaxHelper::class),
        );
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    public function withCode(string $code): TaxRuleBuilder
    {
        $builder = clone $this;
        $builder->taxRule->setCode(code: $code);

        return $builder;
    }

    /**
     * @param int[] $taxRateIds
     *
     * @return $this
     */
    public function withTaxRateIds(array $taxRateIds): TaxRuleBuilder
    {
        $builder = clone $this;
        $builder->taxRule->setTaxRateIds(taxRateIds: $taxRateIds);

        return $builder;
    }

    /**
     * @param int[] $customerTaxClassIds
     *
     * @return $this
     */
    public function withCustomerTaxClassIds(array $customerTaxClassIds): TaxRuleBuilder
    {
        $builder = clone $this;
        $builder->taxRule->setCustomerTaxClassIds(customerTaxClassIds: $customerTaxClassIds);

        return $builder;
    }

    /**
     * @param int[] $productTaxClassIds
     *
     * @return $this
     */
    public function withProductTaxClassIds(array $productTaxClassIds): TaxRuleBuilder
    {
        $builder = clone $this;
        $builder->taxRule->setProductTaxClassIds(productTaxClassIds: $productTaxClassIds);

        return $builder;
    }

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function withPriority(int $priority): TaxRuleBuilder
    {
        $builder = clone $this;
        $builder->taxRule->setPriority(priority: $priority);

        return $builder;
    }

    /**
     * @param bool $calculateSubtotal
     *
     * @return $this
     */
    public function withCalculateSubtotal(bool $calculateSubtotal): TaxRuleBuilder
    {
        $builder = clone $this;
        $builder->taxRule->setCalculateSubtotal(calculateSubtotal: $calculateSubtotal);

        return $builder;
    }

    /**
     * @return TaxRuleInterface
     * @throws \Exception
     */
    public function build(): TaxRuleInterface
    {
        try {
            $builder = $this->createTaxRule();

            return $this->taxRuleRepository->save(rule: $builder->taxRule);
        } catch (\Exception $e) {
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexingFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }
    }

    /**
     * @param int|null $storeId
     *
     * @return TaxRuleBuilder
     */
    private function createTaxRule(?int $storeId = null): TaxRuleBuilder
    {
        $builder = clone $this;

        if (!$builder->taxRule->getCode()) {
            $builder->taxRule->setCode(code: 'klevu_test_tax_code');
        }
        if (!$builder->taxRule->getCustomerTaxClassIds()) {
            $builder->taxRule->setCustomerTaxClassIds(
                customerTaxClassIds: [$this->getDefaultCustomerTaxClassId(storeId: $storeId)],
            );
        }
        if (!$builder->taxRule->getProductTaxClassIds()) {
            $builder->taxRule->setProductTaxClassIds(
                productTaxClassIds: [$this->getDefaultProductTaxClassId()],
            );
        }
        if (!$builder->taxRule->getPriority()) {
            $builder->taxRule->setPriority(priority: 0);
        }
        if (null === $builder->taxRule->getCalculateSubtotal()) {
            $builder->taxRule->setCalculateSubtotal(calculateSubtotal: true);
        }

        return $builder;
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    private function getDefaultCustomerTaxClassId(?int $storeId = null): int
    {
        return (int)$this->taxCalculation->getDefaultCustomerTaxClass(store: $storeId);
    }

    /**
     * @return int
     */
    private function getDefaultProductTaxClassId(): int
    {
        return (int)$this->taxHelper->getDefaultProductTaxClass();
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
            pattern: '{please retry transaction|DDL statements are not allowed in transactions}i',
            subject: $exception->getMessage(),
        );
    }
}
