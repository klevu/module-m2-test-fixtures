<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Helper\Bootstrap;

$indexers = [
    'design_config_grid',
    'customer_grid',
    'catalog_category_product',
    'catalog_product_category',
    'catalogrule_rule',
    'catalog_product_attribute',
    'inventory',
    'catalogrule_product',
    'cataloginventory_stock',
    'targetrule_product_rule',
    'targetrule_rule_product',
    'catalog_product_price',
    'catalogsearch_fulltext',
    'salesrule_rule',
];

/** @var IndexerRegistry $indexerRegistry */
$indexerRegistry = Bootstrap::getObjectManager()->get(IndexerRegistry::class);
foreach ($indexers as $indexerName) {
    try {
        $indexer = $indexerRegistry->get($indexerName);
        $indexer->setScheduled(true);
    } catch (Exception) {
        // this is fine, not all installations will have all indexers
    }
}
