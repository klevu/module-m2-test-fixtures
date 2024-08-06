<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog\Product;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\Data\ProductWebsiteLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductWebsiteLinkRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as ConfigurableOptionsFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory as DownloadableLinkInterfaceFactory;
use Magento\Downloadable\Api\DomainManagerInterface;
use Magento\Downloadable\Api\LinkRepositoryInterface as DownloadableLinkRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Indexer\Model\IndexerFactory;
use Magento\TestFramework\Helper\Bootstrap;
use TddWizard\Fixtures\Catalog\ProductBuilder;

class ConfigurableProductBuilder extends ProductBuilder
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;
    /**
     * @var ConfigurableOptionsFactory
     */
    private ConfigurableOptionsFactory $configurableOptionsFactory;
    /**
     * @var IndexerFactory
     */
    private IndexerFactory $indexerFactory;
    /**
     * @var AttributeInterface[]
     */
    private array $configurableAttributes = [];
    /**
     * @var ProductInterface[]
     */
    private array $variantProducts = [];

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param ProductWebsiteLinkRepositoryInterface $websiteLinkRepository
     * @param ProductWebsiteLinkInterfaceFactory $websiteLinkFactory
     * @param IndexerFactory $indexerFactory
     * @param DownloadableLinkRepositoryInterface $downloadLinkRepository
     * @param DownloadableLinkInterfaceFactory $downloadLinkFactory
     * @param DomainManagerInterface $domainManager
     * @param ConfigurableOptionsFactory $configurableOptionsFactory
     * @param ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param Product $product
     * @param int[] $websiteIds
     * @param mixed[] $storeSpecificValues
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StockItemRepositoryInterface $stockItemRepository,
        ProductWebsiteLinkRepositoryInterface $websiteLinkRepository,
        ProductWebsiteLinkInterfaceFactory $websiteLinkFactory,
        IndexerFactory $indexerFactory,
        DownloadableLinkRepositoryInterface $downloadLinkRepository,
        DownloadableLinkInterfaceFactory $downloadLinkFactory,
        DomainManagerInterface $domainManager,
        ConfigurableOptionsFactory $configurableOptionsFactory,
        ProductTierPriceInterfaceFactory $tierPriceFactory,
        Product $product,
        array $websiteIds,
        array $storeSpecificValues,
    ) {
        parent::__construct(
            productRepository: $productRepository,
            stockItemRepository: $stockItemRepository,
            websiteLinkRepository: $websiteLinkRepository,
            websiteLinkFactory: $websiteLinkFactory,
            indexerFactory: $indexerFactory,
            downloadLinkRepository: $downloadLinkRepository,
            downloadLinkFactory: $downloadLinkFactory,
            domainManager: $domainManager,
            tierPriceFactory: $tierPriceFactory,
            product: $product,
            websiteIds: $websiteIds,
            storeSpecificValues: $storeSpecificValues,
        );

        $this->productRepository = $productRepository;
        $this->indexerFactory = $indexerFactory;
        $this->configurableOptionsFactory = $configurableOptionsFactory;
    }

    /**
     * @return ConfigurableProductBuilder
     */
    public static function aConfigurableProduct(): ConfigurableProductBuilder // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Product $product */
        $product = $objectManager->create(ProductInterface::class);

        // Set some default values. These can be overridden afterwards using with... methods
        $product->setTypeId(Configurable::TYPE_CODE);
        $product->setAttributeSetId(4);
        $product->setName('Configurable Product');
        $product->setVisibility(Visibility::VISIBILITY_BOTH);
        $product->setStatus(Status::STATUS_ENABLED);
        $product->addData([
            'tax_class_id' => 1,
            'description' => 'Description'
            ,
        ]);
        $product->setStockData([
            'manage_stock' => 1,
            'is_in_stock' => 1,
        ]);
        /** @var StockItemInterface $stockItem */
        $stockItem = $objectManager->create(StockItemInterface::class);
        $stockItem->setManageStock(true)
            ->setQty(100)
            ->setIsQtyDecimal(false)
            ->setIsInStock(true);
        $product->setExtensionAttributes(
            $product->getExtensionAttributes()->setStockItem($stockItem),
        );

        return new static(
            $objectManager->create(ProductRepositoryInterface::class),
            $objectManager->create(StockItemRepositoryInterface::class),
            $objectManager->create(ProductWebsiteLinkRepositoryInterface::class),
            $objectManager->create(ProductWebsiteLinkInterfaceFactory::class),
            $objectManager->create(IndexerFactory::class),
            $objectManager->create(DownloadableLinkRepositoryInterface::class),
            $objectManager->create(DownloadableLinkInterfaceFactory::class),
            $objectManager->create(DomainManagerInterface::class),
            $objectManager->create(ConfigurableOptionsFactory::class),
            $objectManager->create(ProductTierPriceInterfaceFactory::class),
            $product,
            [1],
            [],
        );
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return ConfigurableProductBuilder
     */
    public function withConfigurableAttribute(
        AttributeInterface $attribute,
    ): ConfigurableProductBuilder {
        $builder = clone $this;
        $attributeCode = $attribute->getAttributeCode();
        if (!array_key_exists($attributeCode, $builder->configurableAttributes)) {
            $builder->configurableAttributes[$attributeCode] = $attribute;
        }

        return $builder;
    }

    /**
     * @param ProductInterface $variantProduct
     *
     * @return ConfigurableProductBuilder
     */
    public function withVariant(ProductInterface $variantProduct): ConfigurableProductBuilder
    {
        $builder = clone $this;
        $builder->variantProducts[] = $variantProduct;

        return $builder;
    }

    /**
     * @return ProductInterface
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    public function build(): ProductInterface
    {
        $product = parent::build();

        $product = $this->associateChildren(
            configurableProduct: $product,
            configurableAttributes: $this->configurableAttributes,
            variantProducts: $this->variantProducts,
        );

        $indexer = $this->indexerFactory->create();
        $indexerNames = [
            'cataloginventory_stock',
            'catalog_product_price',
        ];
        foreach ($indexerNames as $indexerName) {
            $indexer = $indexer->load($indexerName);
            $indexer->reindexRow($product->getId());
        }

        return $product;
    }

    /**
     * @param ProductInterface $configurableProduct
     * @param AttributeInterface[] $configurableAttributes
     * @param ProductInterface[] $variantProducts
     *
     * @return ProductInterface
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    private function associateChildren(
        ProductInterface $configurableProduct,
        array $configurableAttributes,
        array $variantProducts,
    ): ProductInterface {
        if (!$configurableAttributes) {
            return $configurableProduct;
        }

        $attributeValues = [];
        foreach ($configurableAttributes as $attributeCode => $configurableAttribute) {
            $attributeValues[$attributeCode] = [];

            /** @var Product $variantProduct */
            foreach ($variantProducts as $variantProduct) {
                $attributeCode = $configurableAttribute->getAttributeCode();
                $attributeValues[$attributeCode][] = [
                    'label' => 'test',
                    'attribute_id' => $configurableAttribute->getId(),
                    'value_index' => $variantProduct->getData($attributeCode),
                ];
            }
        }

        $configurableAttributesData = [];
        $position = 0;
        foreach ($attributeValues as $attributeCode => $values) {
            $configurableAttribute = $configurableAttributes[$attributeCode];

            $configurableAttributesData[] = [
                'attribute_id' => $configurableAttribute->getId(),
                'code' => $configurableAttribute->getAttributeCode(),
                'label' => $configurableAttribute->getDataUsingMethod('store_label'),
                'position' => $position++,
                'values' => $values,
            ];
        }

        $extensionConfigurableAttributes = $configurableProduct->getExtensionAttributes();
        if (!$extensionConfigurableAttributes) {
            $objectManager = ObjectManager::getInstance();
            $extensionConfigurableAttributes = $objectManager->create(ProductExtensionInterface::class);
        }

        $configurableOptions = $this->configurableOptionsFactory->create($configurableAttributesData);
        $extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
        $extensionConfigurableAttributes->setConfigurableProductLinks(
            array_map(
                static fn (ProductInterface $variantProduct): int => (int)$variantProduct->getId(),
                $variantProducts,
            ),
        );

        $configurableProduct->setExtensionAttributes($extensionConfigurableAttributes);

        return $this->productRepository->save($configurableProduct);
    }
}
