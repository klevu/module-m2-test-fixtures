<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Traits;

use Magento\Framework\ObjectManagerInterface;

/**
 * @property ObjectManagerInterface $objectManager
 */
trait ObjectInstantiationTrait
{
    /**
     * @var string|null
     */
    private ?string $implementationFqcn = null;
    /**
     * @var string|null
     */
    private ?string $interfaceFqcn = null;
    /**
     * @var bool|null
     */
    private ?bool $expectPlugins = false;
    /**
     * @var mixed[]|null
     */
    private ?array $constructorArgumentDefaults = null;
    /**
     * @var string|null
     */
    private ?string $implementationForVirtualType = null;

    /**
     * @group objectInstantiation
     */
    public function testFqcnResolvesToExpectedImplementation(): object
    {
        try {
            $testObject = $this->instantiateTestObject(
                arguments: $this->constructorArgumentDefaults,
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

        $expectedFqcns = $this->getExpectedFqcns();
        $this->assertContains(
            needle: $testObject::class,
            haystack: $expectedFqcns,
            message: implode(', ', $expectedFqcns),
        );

        return $testObject;
    }

    /**
     * @param mixed[]|null $arguments
     *
     * @return object
     * @throws \LogicException
     */
    private function instantiateTestObject(
        ?array $arguments = null,
    ): object {
        if (!$this->implementationFqcn) {
            throw new \LogicException('Cannot instantiate test object: no implementationFqcn defined');
        }
        if (!(($this->objectManager ?? null) instanceof ObjectManagerInterface)) {
            throw new \LogicException('Cannot instantiate test object: objectManager property not defined');
        }
        if (null === $arguments) {
            $arguments = $this->constructorArgumentDefaults;
        }

        return (null === $arguments)
            ? $this->objectManager->get($this->implementationFqcn)
            : $this->objectManager->create($this->implementationFqcn, $arguments);
    }

    /**
     * @return string[]
     */
    private function getExpectedFqcns(): array
    {
        $expectedFqcns = [
            $this->implementationFqcn,
        ];
        if ($this->implementationForVirtualType) {
            $expectedFqcns[] = $this->implementationForVirtualType;
        }
        if ($this->expectPlugins) {
            $expectedFqcns[] = $this->implementationFqcn . '\Interceptor';
        }

        return $expectedFqcns;
    }
}
