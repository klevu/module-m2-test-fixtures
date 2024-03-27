<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Exception;

class IndexingFailed extends \RuntimeException
{
    // phpcs:disable Magento2.Functions.StaticFunction.StaticFunction
    /**
     * @param \Exception $previous
     *
     * @return self
     */
    public static function becauseInitiallyTriggeredInTransaction(
        \Exception $previous,
    ): self {
        return new self(
            <<<'txt'
The fixture could not be set up because creating index tables does not work within a transaction
You can either run the test without wrapping it in a transaction with:
/**
 * @magentoDbIsolation disabled
 */
Or set the fulltext indexer to "scheduled" before the transaction with:
/**
 * @magentoDataFixtureBeforeTransaction Magento/Catalog/_files/enable_reindex_schedule.php
 */
txt
            ,
            0,
            $previous,
        );
    }
}
