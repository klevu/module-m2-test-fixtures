<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Tax;

use Klevu\TestFixtures\Exception\IndexingFailed;
use Magento\Tax\Api\Data\TaxRateInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

class TaxRateBuilder
{
    /**
     * @var TaxRateInterface
     */
    private TaxRateInterface $taxRate;
    /**
     * @var TaxRateRepositoryInterface
     */
    private TaxRateRepositoryInterface $taxRateRepository;

    /**
     * @param TaxRateInterface $taxRate
     * @param TaxRateRepositoryInterface $taxRateRepository
     */
    public function __construct(
        TaxRateInterface $taxRate,
        TaxRateRepositoryInterface $taxRateRepository,
    ) {
        $this->taxRate = $taxRate;
        $this->taxRateRepository = $taxRateRepository;
    }

    /**
     * @return self
     */
    public static function addTaxRate(): TaxRateBuilder
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            taxRate: $objectManager->create(type: TaxRateInterface::class),
            taxRateRepository: $objectManager->create(type: TaxRateRepositoryInterface::class),
        );
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    public function withCode(string $code): TaxRateBuilder
    {
        $builder = clone $this;
        $builder->taxRate->setCode(code: $code);

        return $builder;
    }

    /**
     * @param float $rate
     *
     * @return $this
     */
    public function withRate(float $rate): TaxRateBuilder
    {
        $builder = clone $this;
        $builder->taxRate->setRate(rate: $rate);

        return $builder;
    }

    /**
     * @param string $countryId
     *
     * @return $this
     */
    public function withCountryId(string $countryId): TaxRateBuilder
    {
        $builder = clone $this;
        $builder->taxRate->setTaxCountryId(taxCountryId: $countryId);

        return $builder;
    }

    /**
     * @param string $taxRegionId
     *
     * @return $this
     */
    public function withRegionId(string $taxRegionId): TaxRateBuilder
    {
        $builder = clone $this;
        $builder->taxRate->setTaxRegionId(taxRegionId: $taxRegionId);

        return $builder;
    }

    /**
     * @param int $zipIsRange
     *
     * @return $this
     */
    public function withZipIsRange(int $zipIsRange): TaxRateBuilder
    {
        $builder = clone $this;
        $builder->taxRate->setZipIsRange(zipIsRange: $zipIsRange);

        return $builder;
    }

    /**
     * @param string $zipFrom
     *
     * @return $this
     */
    public function withZipFrom(string $zipFrom): TaxRateBuilder
    {
        $builder = clone $this;
        $builder->taxRate->setZipFrom(zipFrom: $zipFrom);

        return $builder;
    }

    /**
     * @param string $zipTo
     *
     * @return $this
     */
    public function withZipTo(string $zipTo): TaxRateBuilder
    {
        $builder = clone $this;
        $builder->taxRate->setZipTo(zipTo: $zipTo);

        return $builder;
    }

    /**
     * @param string $taxPostCode
     *
     * @return $this
     */
    public function withTaxPostCode(string $taxPostCode): TaxRateBuilder
    {
        $builder = clone $this;
        $builder->taxRate->setTaxPostcode(taxPostCode: $taxPostCode);

        return $builder;
    }

    /**
     * @return TaxRateInterface
     * @throws \Exception
     */
    public function build(): TaxRateInterface
    {
        try {
            $builder = $this->createTaxRate();

            return $this->taxRateRepository->save(taxRate: $builder->taxRate);
        } catch (\Exception $e) {
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexingFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }
    }

    /**
     * @return TaxRateBuilder
     */
    private function createTaxRate(): TaxRateBuilder
    {
        $builder = clone $this;

        if (!$builder->taxRate->getCode()) {
            $builder->taxRate->setCode(code: 'klevu_test_tax_code');
        }
        if (!$builder->taxRate->getRate()) {
            $builder->taxRate->setRate(rate: 20.00);
        }
        if (!$builder->taxRate->getTaxCountryId()) {
            $builder->taxRate->setTaxCountryId(taxCountryId: 'GB');
        }
        if (!$builder->taxRate->getTaxRegionId()) {
            $builder->taxRate->setTaxRegionId(taxRegionId: 0);
        }
        if ($builder->taxRate->getZipFrom() || $builder->taxRate->getZipTo()) {
            $builder->taxRate->setZipIsRange(zipIsRange: 1);
        }
        if (null === $builder->taxRate->getZipIsRange()) {
            $builder->taxRate->setZipIsRange(zipIsRange: 0);
        }
        if ($builder->taxRate->getZipIsRange()) {
            if (!$builder->taxRate->getZipFrom()) {
                $builder->taxRate->setZipFrom(zipFrom: 0);
            }
            if (!$builder->taxRate->getZipTo()) {
                $builder->taxRate->setZipTo(zipTo: 999999999);
            }
        } elseif (!$builder->taxRate->getTaxPostcode()) {
            $builder->taxRate->setTaxPostcode(taxPostCode: '*');
        }

        return $builder;
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
