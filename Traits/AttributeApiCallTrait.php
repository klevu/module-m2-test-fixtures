<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Traits;

use Klevu\Indexing\Cache\Attributes as AttributesCache;
use Klevu\Indexing\Service\Indexing\AttributesService as AttributesServiceVirtualType;
use Klevu\Indexing\Service\Provider\Sdk\AttributesProvider;
use Klevu\Indexing\Service\Provider\StandardAttributesProvider;
use Klevu\IndexingApi\Service\Provider\Sdk\AttributesProviderInterface;
use Klevu\IndexingApi\Service\Provider\StandardAttributesProviderInterface;
use Klevu\PhpSDK\Api\Model\ApiResponseInterface;
use Klevu\PhpSDK\Api\Model\Indexing\AttributeInterface;
use Klevu\PhpSDK\Api\Service\Indexing\AttributesServiceInterface;
use Klevu\PhpSDK\Model\Indexing\Attribute;
use Klevu\PhpSDK\Model\Indexing\AttributeIterator;
use Klevu\PhpSDK\Model\Indexing\DataType;
use Klevu\PhpSDK\Service\Indexing\AttributesService;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\TypeList;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;

/**
 * @property ObjectManagerInterface $objectManager
 * @method MockBuilder getMockBuilder(string $className)
 * @method InvokedCountMatcher atLeastOnce()
 * @method InvokedCountMatcher never()
 */
trait AttributeApiCallTrait
{
    /**
     * @return void
     */
    private function clearAttributeCache(): void
    {
        $cacheState = $this->objectManager->get(type: StateInterface::class);
        $cacheState->setEnabled(cacheType: AttributesCache::TYPE_IDENTIFIER, isEnabled: true);

        $typeList = $this->objectManager->get(TypeList::class);
        $typeList->cleanType(AttributesCache::TYPE_IDENTIFIER);
    }

    /**
     * @param AttributeInterface[] $attributes
     *
     * @return void
     */
    private function mockSdkAttributeGetApiCall(
        array $attributes = [],
    ): void {
        if (!(($this->objectManager ?? null) instanceof ObjectManagerInterface)) {
            throw new \LogicException('Cannot instantiate test object: objectManager property not defined');
        }
        if (!method_exists($this, 'getMockBuilder')) {
            throw new \LogicException(
                'Method getMockBuilder does not exist. Class must extend PHPUnit\Framework\TestCase',
            );
        }
        $this->removeSharedApiInstances();

        $attributeIterator = $this->objectManager->create(
            type: AttributeIterator::class,
            arguments: [
                'data' => $this->getMockedStandardAttributes(attributes: $attributes),
            ],
        );

        /** @var AttributesServiceInterface&MockObject $mockSdkAttributeService */
        $mockSdkAttributeService = $this->getMockBuilder(AttributesServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockSdkAttributeService->method('get')
            ->willReturn($attributeIterator);

        $this->addSharedApiInstances($mockSdkAttributeService);
    }

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
        $this->removeSharedApiInstances();

        $mockSdkAttributeService = $this->getMockBuilder(AttributesServiceInterface::class)
            ->getMock();

        if ($isCalled) {
            $mockSdkResponse = $this->getMockedSdkResponse($isSuccessful, $message);
            $mockSdkAttributeService->expects($this->atLeastOnce())
                ->method('put')
                ->willReturn($mockSdkResponse);
        } else {
            $mockSdkAttributeService->expects($this->never())
                ->method('put');
        }

        $this->addSharedApiInstances($mockSdkAttributeService);
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
        $this->removeSharedApiInstances();

        $mockSdkAttributeService = $this->getMockBuilder(AttributesServiceInterface::class)
            ->getMock();
        if ($isCalled) {
            $mockSdkResponse = $this->getMockedSdkResponse($isSuccessful, $message);
            $mockSdkAttributeService->expects($this->atLeastOnce())
                ->method('delete')
                ->willReturn($mockSdkResponse);
        } else {
            $mockSdkAttributeService->expects($this->never())
                ->method('delete');
        }

        $this->addSharedApiInstances($mockSdkAttributeService);
    }

    /**
     * @param bool $isCalled
     * @param bool $isSuccessful
     * @param string|null $message
     * @param AttributeInterface[] $attributes
     *
     * @return void
     */
    private function mockSdkAttributeAllApiCall(
        bool $isCalled = true,
        bool $isSuccessful = true,
        ?string $message = null,
        array $attributes = [],
    ): void {
        if (!(($this->objectManager ?? null) instanceof ObjectManagerInterface)) {
            throw new \LogicException('Cannot instantiate test object: objectManager property not defined');
        }
        if (!method_exists($this, 'getMockBuilder')) {
            throw new \LogicException(
                'Method getMockBuilder does not exist. Class must extend PHPUnit\Framework\TestCase',
            );
        }
        $this->removeSharedApiInstances();

        $mockSdkAttributeService = $this->getMockBuilder(AttributesServiceInterface::class)
            ->getMock();
        if ($isCalled) {
            $mockSdkResponse = $this->getMockedSdkResponse($isSuccessful, $message);
            $mockSdkAttributeService->method('delete')
                ->willReturn($mockSdkResponse);
            $mockSdkAttributeService->method('put')
                ->willReturn($mockSdkResponse);
        } else {
            $mockSdkAttributeService->expects($this->never())
                ->method('delete');
            $mockSdkAttributeService->expects($this->never())
                ->method('put');
        }
        $attributeIterator = $this->objectManager->create(
            type: AttributeIterator::class,
            arguments: [
                'data' => $this->getMockedStandardAttributes(attributes: $attributes),
            ],
        );
        $mockSdkAttributeService->method('get')
            ->willReturn($attributeIterator);

        $this->addSharedApiInstances($mockSdkAttributeService);
    }

    /**
     * @param bool $isSuccessful
     * @param string|null $message
     *
     * @return ApiResponseInterface
     */
    private function getMockedSdkResponse(
        bool $isSuccessful = true,
        ?string $message = null,
    ): ApiResponseInterface {
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

        return $mockSdkResponse;
    }

    /**
     * @param AttributesServiceInterface|MockObject $mockSdkAttributeService
     *
     * @return void
     */
    private function addSharedApiInstances(AttributesServiceInterface | MockObject $mockSdkAttributeService): void
    {
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
     * @return void
     */
    private function removeSharedApiInstances(): void
    {
        $this->objectManager->removeSharedInstance(
            className: AttributesServiceVirtualType::class,
        );
        $this->objectManager->removeSharedInstance(
            className: AttributesService::class,
        );
        // remove StandardAttributesProviderInterface as it uses AttributesService,
        // which gets cached if not removed
        $this->objectManager->removeSharedInstance(
            className: AttributesProvider::class,
        );
        $this->objectManager->removeSharedInstance(
            className: AttributesProviderInterface::class,
        );
        $this->objectManager->removeSharedInstance(
            className: StandardAttributesProvider::class,
        );
        $this->objectManager->removeSharedInstance(
            className: StandardAttributesProviderInterface::class,
        );
    }

    /**
     * @param AttributeInterface[] $attributes
     *
     * @return AttributeInterface[]
     */
    private function getMockedStandardAttributes(array $attributes): array
    {
        $return = [];

        $return['additionalDataToReturn'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'additionalDataToReturn',
                'datatype' => DataType::JSON->value,
                'label' => [
                    'default' => 'Display',
                ],
                'searchable' => false,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['category'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'category',
                'datatype' => DataType::STRING->value,
                'label' => [
                    'default' => 'Category',
                ],
                'searchable' => true,
                'filterable' => true,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['currency'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'currency',
                'datatype' => DataType::STRING->value,
                'label' => [
                    'default' => 'Currency',
                ],
                'searchable' => false,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['description'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'description',
                'datatype' => DataType::STRING->value,
                'label' => [
                    'default' => 'Description',
                ],
                'searchable' => true,
                'filterable' => false,
                'returnable' => false,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [
                    'desc',
                ],
                'immutable' => true,
            ],
        );
        $return['id'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'id',
                'datatype' => DataType::NUMBER->value,
                'label' => [
                    'default' => 'Id',
                ],
                'searchable' => false,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['image'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'image',
                'datatype' => DataType::STRING->value,
                'label' => [
                    'default' => 'Image',
                ],
                'searchable' => false,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['imageHover'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'imageHover',
                'datatype' => DataType::STRING->value,
                'label' => [
                    'default' => 'Image Hover',
                ],
                'searchable' => false,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['inStock'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'inStock',
                'datatype' => DataType::BOOLEAN->value,
                'label' => [
                    'default' => 'In Stock',
                ],
                'searchable' => false,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['itemGroupId'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'itemGroupId',
                'datatype' => DataType::STRING->value,
                'label' => [
                    'default' => 'Item Group Id',
                ],
                'searchable' => false,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['listCategory'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'listCategory',
                'datatype' => DataType::STRING->value,
                'label' => [
                    'default' => 'List Category',
                ],
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['name'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'name',
                'datatype' => DataType::STRING->value,
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['price'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'price',
                'datatype' => DataType::NUMBER->value,
                'label' => [
                    'default' => 'Price',
                ],
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['rating'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'rating',
                'datatype' => DataType::NUMBER->value,
                'label' => [
                    'default' => 'Rating',
                ],
                'searchable' => false,
                'filterable' => true,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['ratingCount'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'ratingCount',
                'datatype' => DataType::NUMBER->value,
                'label' => [
                    'default' => 'Rating Count',
                ],
                'searchable' => false,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['salePrice'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'salePrice',
                'datatype' => DataType::NUMBER->value,
                'label' => [
                    'default' => 'Sale Price',
                ],
                'searchable' => false,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['shortDescription'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'shortDescription',
                'datatype' => DataType::STRING->value,
                'label' => [
                    'default' => 'Short Description',
                ],
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [
                    'shortDesc',
                ],
                'immutable' => true,
            ],
        );
        $return['sku'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'sku',
                'datatype' => DataType::STRING->value,
                'label' => [
                    'default' => 'SKU',
                ],
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['startPrice'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'startPrice',
                'datatype' => DataType::NUMBER->value,
                'label' => [
                    'default' => 'Start Price',
                ],
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['swatchesInfo'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'swatchesInfo',
                'datatype' => DataType::STRING->value,
                'label' => [
                    'default' => 'Swatch info',
                ],
                'searchable' => false,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['tags'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'tags',
                'datatype' => DataType::MULTIVALUE->value,
                'label' => [
                    'default' => 'Tags',
                ],
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['toPrice'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'toPrice',
                'datatype' => DataType::NUMBER->value,
                'label' => [
                    'default' => 'To Price',
                ],
                'searchable' => false,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['url'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'url',
                'datatype' => DataType::STRING->value,
                'label' => [
                    'default' => 'URL',
                ],
                'searchable' => false,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        $return['visibility'] = $this->objectManager->create(
            type: Attribute::class,
            arguments: [
                'attributeName' => 'visibility',
                'datatype' => DataType::MULTIVALUE->value,
                'label' => [
                    'default' => 'Visibility',
                ],
                'searchable' => true,
                'filterable' => false,
                'returnable' => true,
                'abbreviate' => false,
                'rangeable' => false,
                'aliases' => [],
                'immutable' => true,
            ],
        );
        foreach ($attributes as $key => $attribute) {
            $return[$key] = $attribute;
        }

        return $return;
    }
}
