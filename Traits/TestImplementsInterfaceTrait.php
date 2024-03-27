<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Traits;

trait TestImplementsInterfaceTrait
{
    /**
     * @group objectInstantiation
     */
    public function testImplementsExpectedInterface(): object
    {
        // @see TestInterfacePreferenceTrait
        if (method_exists($this, 'instantiateTestObjectFromInterface')) {
            try {
                $this->assertInstanceOf(
                    $this->interfaceFqcn,
                    $this->instantiateTestObjectFromInterface(
                        arguments: $this->constructorArgumentDefaults,
                    ),
                );
            } catch (\Exception $exception) {
                $this->fail(
                    sprintf(
                        'Cannot instantiate test object from interface "%s": %s',
                        $this->interfaceFqcn,
                        $exception->getMessage(),
                    ),
                );
            }
        }

        try {
            $this->assertInstanceOf(
                $this->interfaceFqcn,
                $testObject = $this->instantiateTestObject(
                    arguments: $this->constructorArgumentDefaults,
                ),
            );
        } catch (\Exception $exception) {
            $this->fail(
                sprintf(
                    'Cannot instantiate test object from FQCN "%s": %s',
                    $this->implementationFqcn,
                    $exception->getMessage(),
                ),
            );
        }

        return $testObject;
    }
}
