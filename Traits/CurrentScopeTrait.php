<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Traits;

use Klevu\Configuration\Model\CurrentScopeFactory;
use Klevu\Configuration\Model\CurrentScopeInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\TestFramework\Helper\Bootstrap;

trait CurrentScopeTrait
{
    /**
     * @param StoreInterface|WebsiteInterface|null $scope
     *
     * @return CurrentScopeInterface|mixed
     */
    private function createCurrentScope(StoreInterface|WebsiteInterface|null $scope): mixed
    {
        $objectManager = Bootstrap::getObjectManager();
        $currentScopeFactory = $objectManager->get(CurrentScopeFactory::class);
        if ($scope instanceof StoreInterface) {
            return $currentScopeFactory->create([
                'scopeObject' => $scope,
            ]);
        }

        return $currentScopeFactory->create([
            'scopeObject' => $scope,
        ]);
    }
}
