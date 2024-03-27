<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Traits;

use Magento\Framework\ObjectManagerInterface;

trait TestInterfacePreferenceTrait
{
    /**
     * @group objectInstantiation
     */
    public function testInterfacePreferenceResolvesToExpectedImplementation(): void
    {
        try {
            $testObjectFromInterface = $this->instantiateTestObjectFromInterface(
                $this->constructorArgumentDefaults,
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

        $expectedFqcns = [
            $this->implementationFqcn,
        ];
        if ($this->expectPlugins) {
            $expectedFqcns[] = $this->implementationFqcn . '\Interceptor';
        }

        $this->assertContains(
            $testObjectFromInterface::class,
            $expectedFqcns,
            implode(', ', $expectedFqcns),
        );
    }

    /**
     * @param mixed[]|null $arguments
     * @return object
     * @throws \LogicException
     */
    private function instantiateTestObjectFromInterface(
        ?array $arguments = null,
    ): object {
        if (!$this->interfaceFqcn) {
            throw new \LogicException('Cannot instantiate test object: no implementationFqcn defined');
        }
        if (!(($this->objectManager ?? null) instanceof ObjectManagerInterface)) {
            throw new \LogicException('Cannot instantiate test object: objectManager property not defined');
        }

        return (null === $arguments)
            ? $this->objectManager->get($this->interfaceFqcn)
            : $this->objectManager->create($this->interfaceFqcn, $arguments);
    }
}
