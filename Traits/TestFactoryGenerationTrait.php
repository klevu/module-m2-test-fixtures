<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Traits;

trait TestFactoryGenerationTrait
{
    /**
     * @group objectInstantiation
     */
    public function testImplementationGeneratesFromFactory(): object {
        try {
            $testObjectFactory = $this->objectManager->get(
                $this->implementationFqcn . 'Factory',
            );
        } catch (\Exception $exception) {
            $this->fail(
                sprintf(
                    'Cannot instantiate object factory from FQCN "%sFactory": %s',
                    $this->implementationFqcn,
                    $exception->getMessage(),
                ),
            );
        }

        $this->assertNotNull($testObjectFactory);
        $this->assertTrue(
            method_exists($testObjectFactory, 'create'),
        );

        $testObject = $testObjectFactory->create(
            $this->constructorArgumentDefaults ?? [],
        );

        $expectedFqcns = $this->getExpectedFqcns();
        $this->assertContains(
            $testObject::class,
            $expectedFqcns,
            implode(', ', $expectedFqcns),
        );

        return $testObject;
    }
}
