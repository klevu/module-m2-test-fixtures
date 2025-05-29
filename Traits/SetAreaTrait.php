<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Traits;

use Magento\Framework\App\Area;
use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\State;
use Magento\Framework\Config\Scope;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Application;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @property ObjectManagerInterface $objectManager
 */
trait SetAreaTrait
{
    /**
     * Janky workaround for when magentoAppArea doesn't work
     *
     * @param $areaCode
     *
     * @return void
     * @throws LocalizedException
     */
    private function setArea($areaCode): void
    {
        if (!(($this->objectManager ?? null) instanceof ObjectManagerInterface)) {
            throw new \LogicException('Cannot instantiate test object: objectManager property not defined');
        }

        try {
            /** @var State $state */
            $state = $this->objectManager->get(State::class);
            $state->setAreaCode(
                code: $areaCode,
            );
        } catch (LocalizedException) {
            // Area code is already set
            return;
        }

        /** @var Scope $scope */
        $scope = $this->objectManager->get(Scope::class);
        $scope->setCurrentScope($areaCode);

        /** @var ConfigLoader $configLoader */
        $configLoader = $this->objectManager->get(ConfigLoader::class);
        $loadedConfig = $configLoader->load($areaCode);
        $this->objectManager->configure($loadedConfig);

        $areaList = $this->objectManager->get(AreaList::class);

        switch ($areaCode) {
            case Application::DEFAULT_APP_AREA:
            case Area::AREA_CRONTAB:
            case Area::AREA_WEBAPI_REST:
                $area = $areaList->getArea($areaCode);
                $area->load(AreaInterface::PART_CONFIG);
                break;

            default:
                $bootstrap = Bootstrap::getInstance();
                $bootstrap->loadArea($areaCode);
                break;
        }
    }
}
