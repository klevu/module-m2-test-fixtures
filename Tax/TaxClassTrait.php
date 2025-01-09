<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Tax;

use Klevu\TestFixtures\Exception\FixturePoolMissingException;

trait TaxClassTrait
{
    /**
     * @var TaxClassFixturePool|null
     */
    private ?TaxClassFixturePool $taxClassFixturePool = null;

    /**
     * @param mixed[]|null $taxClassData
     *
     * @return void
     * @throws \Exception
     */
    public function createTaxClass(?array $taxClassData = []): void
    {
        if (null === $this->taxClassFixturePool) {
            throw new FixturePoolMissingException(
                message: 'taxClassFixturePool has not been created in your test setUp method.',
            );
        }
        $taxClassBuilder = TaxClassBuilder::addTaxClass();
        $taxClassBuilder->withClassName(className: $taxClassData['class_name']);
        $taxClassBuilder->withClassType(classType: $taxClassData['class_type']);
        $this->taxClassFixturePool->add(
            taxClass: $taxClassBuilder->build(),
            key: $taxClassData['key'] ?? 'test_tax_class',
        );
    }
}
