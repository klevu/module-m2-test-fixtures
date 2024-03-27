<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Traits;

use Klevu\Indexing\Service\Indexing\AttributesService as AttributesServiceVirtualType;
use Klevu\PhpSDK\Api\Model\ApiResponseInterface;
use Klevu\PhpSDK\Api\Service\Indexing\AttributesServiceInterface;
use Klevu\PhpSDK\Service\Indexing\AttributesService;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;

/**
 * @property ObjectManagerInterface $objectManager
 * @method MockBuilder getMockBuilder(string $className)
 * @method InvokedCountMatcher atLeastOnce()
 * @method InvokedCountMatcher never()
 */
trait PipelineAttributeApiCallTrait
{
    /**
     * @param bool $isCalled
     * @param bool $isSuccessful
     * @param string|null $message
     *
     * @return void
     */
    private function mockSdkAttributePutApiCall(
        bool $isCalled = true,
        bool $isSuccessful = true,
        ?string $message = null,
    ): void {
        if (!(($this->objectManager ?? null) instanceof ObjectManagerInterface)) {
            throw new \LogicException('Cannot instantiate test object: objectManager property not defined');
        }
        if (!method_exists($this, 'getMockBuilder')) {
            throw new \LogicException(
                'Method getMockBuilder does not exist. Class must extend PHPUnit\Framework\TestCase',
            );
        }
        $mockSdkAttributeService = $this->getMockBuilder(AttributesServiceInterface::class)
            ->getMock();

        if ($isCalled) {
            $mockSdkResponse = $this->getMockBuilder(ApiResponseInterface::class)
                ->getMock();
            $mockSdkResponse->method('isSuccess')
                ->willReturn($isSuccessful);
            if ($isSuccessful) {
                $mockSdkResponse->method('getResponseCode')
                    ->willReturn(200);
            }
            $defaultMessage = $isSuccessful
                ? 'Batch accepted successfully'
                : 'There has been an ERROR';
            $mockSdkResponse->method('getMessages')
                ->willReturn([$message ?? $defaultMessage]);
            $mockSdkAttributeService->expects($this->atLeastOnce())
                ->method('put')
                ->wilLReturn($mockSdkResponse);
        } else {
            $mockSdkAttributeService->expects($this->never())
                ->method('put');
        }

        $this->objectManager->addSharedInstance(
            instance: $mockSdkAttributeService,
            className: AttributesService::class,
        );
        $this->objectManager->addSharedInstance(
            instance: $mockSdkAttributeService,
            className: AttributesServiceVirtualType::class,
        );
    }

    /**
     * @param bool $isCalled
     * @param bool $isSuccessful
     *
     * @return void
     */
    private function mockSdkAttributeDeleteApiCall(
        bool $isCalled = true,
        bool $isSuccessful = true,
        ?string $message = null,
    ): void {
        if (!(($this->objectManager ?? null) instanceof ObjectManagerInterface)) {
            throw new \LogicException('Cannot instantiate test object: objectManager property not defined');
        }
        if (!method_exists($this, 'getMockBuilder')) {
            throw new \LogicException(
                'Method getMockBuilder does not exist. Class must extend PHPUnit\Framework\TestCase',
            );
        }
        $mockSdkAttributeService = $this->getMockBuilder(AttributesServiceInterface::class)
            ->getMock();
        if ($isCalled) {
            $mockSdkResponse = $this->getMockBuilder(ApiResponseInterface::class)
                ->getMock();
            $mockSdkResponse->method('isSuccess')
                ->willReturn($isSuccessful);
            if ($isSuccessful) {
                $mockSdkResponse->method('getResponseCode')
                    ->willReturn(200);
            }
            $defaultMessage = $isSuccessful
                ? 'Batch accepted successfully'
                : 'There has been an ERROR';
            $mockSdkResponse->method('getMessages')
                ->willReturn([$message ?? $defaultMessage]);
            $mockSdkAttributeService->expects($this->atLeastOnce())
                ->method('delete')
                ->wilLReturn($mockSdkResponse);
        } else {
            $mockSdkAttributeService->expects($this->never())
                ->method('delete');
        }

        $this->objectManager->addSharedInstance(
            instance: $mockSdkAttributeService,
            className: AttributesService::class,
        );
        $this->objectManager->addSharedInstance(
            instance: $mockSdkAttributeService,
            className: AttributesServiceVirtualType::class,
        );
    }

    private function mockSdkAttributeAllApiCall(
        bool $isCalled = true,
        bool $isSuccessful = true,
        ?string $message = null,
    ): void {
        if (!(($this->objectManager ?? null) instanceof ObjectManagerInterface)) {
            throw new \LogicException('Cannot instantiate test object: objectManager property not defined');
        }
        if (!method_exists($this, 'getMockBuilder')) {
            throw new \LogicException(
                'Method getMockBuilder does not exist. Class must extend PHPUnit\Framework\TestCase',
            );
        }
        $mockSdkAttributeService = $this->getMockBuilder(AttributesServiceInterface::class)
            ->getMock();
        if ($isCalled) {
            $mockSdkResponse = $this->getMockBuilder(ApiResponseInterface::class)
                ->getMock();
            $mockSdkResponse->method('isSuccess')
                ->willReturn($isSuccessful);
            if ($isSuccessful) {
                $mockSdkResponse->method('getResponseCode')
                    ->willReturn(200);
            }
            $defaultMessage = $isSuccessful
                ? 'Batch accepted successfully'
                : 'There has been an ERROR';
            $mockSdkResponse->method('getMessages')
                ->willReturn([$message ?? $defaultMessage]);
            $mockSdkAttributeService->expects($this->atLeastOnce())
                ->method('delete')
                ->wilLReturn($mockSdkResponse);
            $mockSdkAttributeService->expects($this->atLeastOnce())
                ->method('put')
                ->wilLReturn($mockSdkResponse);
        } else {
            $mockSdkAttributeService->expects($this->never())
                ->method('delete');
            $mockSdkAttributeService->expects($this->never())
                ->method('put');
        }

        $this->objectManager->addSharedInstance(
            instance: $mockSdkAttributeService,
            className: AttributesService::class,
        );
        $this->objectManager->addSharedInstance(
            instance: $mockSdkAttributeService,
            className: AttributesServiceVirtualType::class,
        );
    }
}
