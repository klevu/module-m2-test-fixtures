<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Traits;

use Klevu\PhpSDK\Api\Model\ApiResponseInterface;
use Klevu\PhpSDK\Api\Service\Indexing\BatchDeleteServiceInterface;
use Klevu\PhpSDK\Api\Service\Indexing\BatchServiceInterface;
use Klevu\PhpSDK\Service\Indexing\Batch\DeleteService as BatchDeleteService;
use Klevu\PhpSDK\Service\Indexing\BatchService;
use Klevu\PlatformPipelines\ObjectManager\Container;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;

/**
 * @property ObjectManagerInterface $objectManager
 * @method MockBuilder getMockBuilder(string $className)
 * @method InvokedCountMatcher atLeastOnce()
 * @method InvokedCountMatcher never()
 */
trait PipelineEntityApiCallTrait
{
    /**
     * @param bool $isCalled
     * @param bool $isSuccessful
     *
     * @return void
     */
    private function mockBatchServicePutApiCall(
        bool $isCalled = true,
        bool $isSuccessful = true,
    ): void {
        if (!(($this->objectManager ?? null) instanceof ObjectManagerInterface)) {
            throw new \LogicException('Cannot instantiate test object: objectManager property not defined');
        }
        if (!method_exists($this, 'getMockBuilder')) {
            throw new \LogicException(
                'Method getMockBuilder does not exist. Class must extend PHPUnit\Framework\TestCase',
            );
        }
        $mockBatchService = $this->getMockBuilder(BatchServiceInterface::class)
            ->getMock();
        if ($isCalled) {
            $mockApiResponse = $this->getMockBuilder(ApiResponseInterface::class)
                ->getMock();
            $mockApiResponse->method('isSuccess')
                ->willReturn($isSuccessful);

            $message = $isSuccessful
                ? 'Batch accepted successfully'
                : 'There has been an ERROR';
            $mockApiResponse->method('getMessages')
                ->willReturn([$message]);

            $mockBatchService->expects($this->atLeastOnce())
                ->method('send')
                ->willReturn($mockApiResponse);
        } else {
            $mockBatchService->expects($this->never())
                ->method('send');
        }

        $container = $this->objectManager->get(Container::class);
        $container->addSharedInstance(
            identifier: BatchServiceInterface::class,
            instance: $mockBatchService,
        );
        $container->addSharedInstance(
            identifier: BatchService::class,
            instance: $mockBatchService,
        );
    }

    /**
     * @param bool $isCalled
     * @param bool $isSuccessful
     *
     * @return void
     */
    private function mockBatchServiceDeleteApiCall(
        bool $isCalled = true,
        bool $isSuccessful = true,
    ): void {
        if (!(($this->objectManager ?? null) instanceof ObjectManagerInterface)) {
            throw new \LogicException('Cannot instantiate test object: objectManager property not defined');
        }
        if (!method_exists($this, 'getMockBuilder')) {
            throw new \LogicException(
                'Method getMockBuilder does not exist. Class must extend PHPUnit\Framework\TestCase',
            );
        }
        $mockBatchService = $this->getMockBuilder(BatchDeleteServiceInterface::class)
            ->getMock();
        if ($isCalled) {
            $mockApiResponse = $this->getMockBuilder(ApiResponseInterface::class)
                ->getMock();
            $mockApiResponse->method('isSuccess')
                ->willReturn($isSuccessful);
            $message = $isSuccessful
                ? 'Batch accepted successfully'
                : 'There has been an ERROR';
            $mockApiResponse->method('getMessages')
                ->willReturn([$message]);

            $mockBatchService->expects($this->atLeastOnce())
                ->method('sendByIds')
                ->willReturn($mockApiResponse);
        } else {
            $mockBatchService->expects($this->never())
                ->method('sendByIds');
        }

        $container = $this->objectManager->get(Container::class);
        $container->addSharedInstance(
            identifier: BatchDeleteServiceInterface::class,
            instance: $mockBatchService,
        );
        $container->addSharedInstance(
            identifier: BatchDeleteService::class,
            instance: $mockBatchService,
        );
    }
}
