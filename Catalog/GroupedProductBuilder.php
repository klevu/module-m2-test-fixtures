<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\Data\ProductWebsiteLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductWebsiteLinkRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Downloadable\Api\Data\LinkInterfaceFactory as DownloadableLinkInterfaceFactory;
use Magento\Downloadable\Api\DomainManagerInterface;
use Magento\Downloadable\Api\LinkRepositoryInterface as DownloadableLinkRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Indexer\Model\IndexerFactory;
use Magento\TestFramework\Helper\Bootstrap;
use TddWizard\Fixtures\Catalog\ProductBuilder;
use TddWizard\Fixtures\Catalog\ProductFixture;

class GroupedProductBuilder extends ProductBuilder
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;
    /**
     * @var IndexerFactory
     */
    private IndexerFactory $indexerFactory;
    /**
     * @var ProductLinkInterfaceFactory|null
     */
    private ?ProductLinkInterfaceFactory $productLinkFactory = null;
    /**
     * @var ProductBuilder[]
     */
    private array $linkedProducts = [];
    /**
     * @var ProductFixture[]
     */
    private array $linkedProductFixtures = [];

    /**
     * @return GroupedProductBuilder
     */
    public static function aGroupedProduct(): GroupedProductBuilder
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Product $product */
        $product = $objectManager->create(Product::class);

        $product->setTypeId(Grouped::TYPE_CODE);
        $product->setAttributeSetId(4);
        $product->setName('Grouped product');
        $product->setVisibility(Visibility::VISIBILITY_BOTH);
        $product->setStatus(Status::STATUS_ENABLED);
        $product->addData([
            'tax_class_id' => 1,
            'description' => 'Description',
        ]);
        $product->setStockData([
            'manage_stock' => 0,
            'is_in_stock' => 1,
        ]);
        /** @var StockItemInterface $stockItem */
        $stockItem = $objectManager->create(StockItemInterface::class);
        $stockItem->setManageStock(false)
            ->setQty(100)
            ->setIsQtyDecimal(false)
            ->setIsInStock(true);
        $product->setExtensionAttributes(
            $product->getExtensionAttributes()->setStockItem($stockItem),
        );

        return self::getBuilderReturnObject(
            product: $product,
        );
    }

    /**
     * @param ProductBuilder $linkedProductBuilder
     * @return $this
     */
    public function withLinkedProduct(ProductBuilder $linkedProductBuilder): GroupedProductBuilder
    {
        $builder = clone $this;
        $builder->linkedProducts[] = $linkedProductBuilder;

        return $builder;
    }

    /**
     * @param ProductFixture $linkedProductFixture
     * @return $this
     */
    public function withLinkedProductFixtures(ProductFixture $linkedProductFixture): GroupedProductBuilder
    {
        $builder = clone $this;
        $builder->linkedProductFixtures[] = $linkedProductFixture;

        return $builder;
    }

    /**
     * @return ProductInterface
     * @throws \Exception
     */
    public function build(): ProductInterface
    {
        $linkedProducts = array_map(
            static fn (ProductBuilder $productBuilder): ProductInterface => $productBuilder->build(),
            $this->linkedProducts,
        );
        $linkedProductsFromFixtures = array_map(
            static fn (ProductFixture $productFixture): ProductInterface => $productFixture->getProduct(),
            $this->linkedProductFixtures,
        );
        $linkedProducts = array_merge($linkedProducts, $linkedProductsFromFixtures);

        $product = parent::build();

        $this->associateLinkedProducts(
            parentProduct: $product,
            linkedProducts: $linkedProducts,
        );
        $indexers = [
            'cataloginventory_stock',
            'catalog_product_price',
        ];
        foreach ($indexers as $indexerName) {
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerName);
            $indexer->reindexRow($product->getId());
        }

        return $product;
    }

    /**
     * @param ProductInterface $parentProduct
     * @param ProductInterface[] $linkedProducts
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     */
    private function associateLinkedProducts(
        ProductInterface $parentProduct,
        array $linkedProducts,
    ): void {
        $productLinks = [];

        $position = 1;
        foreach ($linkedProducts as $linkedProduct) {
            $productLink = $this->productLinkFactory->create();
            $productLink->setSku($parentProduct->getSku());
            $productLink->setLinkType('associated');
            $productLink->setLinkedProductSku($linkedProduct->getSku());
            $productLink->setPosition($position++);

            $extensionAttributes = $productLink->getExtensionAttributes();
            $extensionAttributes->setQty(1);

            $productLinks[] = $productLink;
        }

        $parentProduct->setProductLinks($productLinks);

        $this->productRepository->save($parentProduct);
    }

    /**
     * @param Product $product
     * @return GroupedProductBuilder
     */
    private static function getBuilderReturnObject( // phpcs:ignore Magento2.Functions.StaticFunction.StaticFunction, Generic.Files.LineLength.TooLong
        Product $product,
    ): GroupedProductBuilder {
        $objectManager = Bootstrap::getObjectManager();

        $productRepository = $objectManager->create(ProductRepositoryInterface::class);
        $indexerFactory = $objectManager->create(IndexerFactory::class);

        $return = new static(
            productRepository: $productRepository,
            stockItemRepository: $objectManager->create(StockItemRepositoryInterface::class),
            websiteLinkRepository: $objectManager->create(ProductWebsiteLinkRepositoryInterface::class),
            websiteLinkFactory: $objectManager->create(ProductWebsiteLinkInterfaceFactory::class),
            indexerFactory: $indexerFactory,
            downloadLinkRepository: $objectManager->create(DownloadableLinkRepositoryInterface::class),
            downloadLinkFactory: $objectManager->create(DownloadableLinkInterfaceFactory::class),
            domainManager: $objectManager->create(DomainManagerInterface::class),
            tierPriceFactory: $objectManager->create(ProductTierPriceInterfaceFactory::class),
            product: $product,
            websiteIds: [1],
            storeSpecificValues: [],
        );

        // Private properties we also need
        $return->productRepository = $productRepository;
        $return->indexerFactory = $indexerFactory;

        // We can't extend the parent constructor, so these live here now
        $return->productLinkFactory = $objectManager->get(ProductLinkInterfaceFactory::class);

        return $return;
    }
}
