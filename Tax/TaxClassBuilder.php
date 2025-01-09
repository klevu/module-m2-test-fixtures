<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Tax;

use Klevu\TestFixtures\Exception\IndexingFailed;
use Magento\Tax\Api\Data\TaxClassInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

class TaxClassBuilder
{
    /**
     * @var TaxClassInterface
     */
    private TaxClassInterface $taxClass;
    /**
     * @var TaxClassRepositoryInterface
     */
    private TaxClassRepositoryInterface $taxClassRepository;

    /**
     * @param TaxClassInterface $taxClass
     * @param TaxClassRepositoryInterface $taxClassRepository
     */
    public function __construct(
        TaxClassInterface $taxClass,
        TaxClassRepositoryInterface $taxClassRepository,
    ) {
        $this->taxClass = $taxClass;
        $this->taxClassRepository = $taxClassRepository;
    }

    /**
     * @return self
     */
    public static function addTaxClass(): TaxClassBuilder
    {
        $objectManager = Bootstrap::getObjectManager();

        return new self(
            taxClass: $objectManager->create(type: TaxClassInterface::class),
            taxClassRepository: $objectManager->create(type: TaxClassRepositoryInterface::class),
        );
    }

    /**
     * @param string $className
     *
     * @return $this
     */
    public function withClassName(string $className): TaxClassBuilder
    {
        $builder = clone $this;
        $builder->taxClass->setClassName(className: $className);

        return $builder;
    }

    /**
     * @param string $classType
     *
     * @return $this
     */
    public function withClassType(string $classType): TaxClassBuilder
    {
        $builder = clone $this;
        $builder->taxClass->setClassType(classType: $classType);

        return $builder;
    }

    /**
     * @return TaxClassInterface
     * @throws \Exception
     */
    public function build(): TaxClassInterface
    {
        try {
            $builder = $this->createTaxClass();
            $taxClassId = $this->taxClassRepository->save(taxClass:$builder->taxClass);

            return $this->taxClassRepository->get(taxClassId: $taxClassId);
        } catch (\Exception $e) {
            if (self::isTransactionException($e) || self::isTransactionException($e->getPrevious())) {
                throw IndexingFailed::becauseInitiallyTriggeredInTransaction($e);
            }
            throw $e;
        }
    }

    /**
     * @return TaxClassBuilder
     */
    private function createTaxClass(): TaxClassBuilder
    {
        $builder = clone $this;

        if (!$builder->taxClass->getClassName()) {
            $builder->taxClass->setClassName(className: 'Klevu Product Tax Class');
        }
        if (!$builder->taxClass->getClassType()) {
            $builder->taxClass->setClassType(classType: 'PRODUCT');
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
            '{please retry transaction|DDL statements are not allowed in transactions}i',
            $exception->getMessage(),
        );
    }
}
