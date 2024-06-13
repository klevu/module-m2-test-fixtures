<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\TestFixtures\Traits;

use Klevu\Configuration\Service\Provider\ApiKeyProvider;
use Klevu\Configuration\Service\Provider\AuthKeyProvider;
use Klevu\Configuration\Service\Provider\ScopeProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\Writer as ConfigWriter;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

trait SetAuthKeysTrait
{
    /**
     * @param ScopeProviderInterface $scopeProvider
     * @param string|null $jsApiKey
     * @param string|null $restAuthKey
     * @param bool $removeApiKeys
     * @param bool|null $singleStoreMode
     *
     * @return void
     */
    private function setAuthKeys(
        ScopeProviderInterface $scopeProvider,
        ?string $jsApiKey = null,
        ?string $restAuthKey = null,
        bool $removeApiKeys = true,
        ?bool $singleStoreMode = false,
    ): void {
        if (!(($this->objectManager ?? null) instanceof ObjectManagerInterface)) {
            throw new \LogicException('Cannot instantiate test object: objectManager property not defined');
        }
        if ($removeApiKeys) {
            $this->removeAuthKeys();
        }

        $scope = $scopeProvider->getCurrentScope();
        /** @var ConfigWriter $configWriter */
        $configWriter = $this->objectManager->get(ConfigWriter::class);
        if (null !== $jsApiKey) {
            $configWriter->save(
                path: ApiKeyProvider::CONFIG_XML_PATH_JS_API_KEY,
                value: $jsApiKey,
                scope: $singleStoreMode
                    ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                    : $scope->getScopeType(),
                scopeId: $singleStoreMode
                    ? Store::DEFAULT_STORE_ID
                    : $scope?->getScopeId() ?? Store::DEFAULT_STORE_ID,
            );
        }
        if (null !== $restAuthKey) {
            $configWriter->save(
                path: AuthKeyProvider::CONFIG_XML_PATH_REST_AUTH_KEY,
                value: $restAuthKey,
                scope: $singleStoreMode
                    ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                    : $scope->getScopeType(),
                scopeId: $singleStoreMode
                    ? Store::DEFAULT_STORE_ID
                    : $scope?->getScopeId() ?? Store::DEFAULT_STORE_ID,
            );
        }
        $this->clearConfigCache();
    }

    /**
     * @return void
     */
    private function removeAuthKeys(): void
    {
        if (!(($this->objectManager ?? null) instanceof ObjectManagerInterface)) {
            throw new \LogicException('Cannot instantiate test object: objectManager property not defined');
        }
        /** @var ConfigWriter $configWriter */
        $configWriter = $this->objectManager->get(ConfigWriter::class);
        /** @var StoreManagerInterface $storeManager */
        $storeManager = $this->objectManager->get(StoreManagerInterface::class);
        foreach ($storeManager->getWebsites() as $website) {
            $configWriter->delete(
                path: ApiKeyProvider::CONFIG_XML_PATH_JS_API_KEY,
                scope: ScopeInterface::SCOPE_WEBSITES,
                scopeId: $website->getId(),
            );
            $configWriter->delete(
                path: AuthKeyProvider::CONFIG_XML_PATH_REST_AUTH_KEY,
                scope: ScopeInterface::SCOPE_WEBSITES,
                scopeId: $website->getId(),
            );
        }
        foreach ($storeManager->getStores() as $store) {
            $configWriter->delete(
                path: ApiKeyProvider::CONFIG_XML_PATH_JS_API_KEY,
                scope: ScopeInterface::SCOPE_STORES,
                scopeId: $store->getId(),
            );
            $configWriter->delete(
                path: AuthKeyProvider::CONFIG_XML_PATH_REST_AUTH_KEY,
                scope: ScopeInterface::SCOPE_STORES,
                scopeId: $store->getId(),
            );
        }
        $this->clearConfigCache();
    }

    /**
     * @return void
     */
    private function clearConfigCache(): void
    {
        $config = $this->objectManager->get(ScopeConfigInterface::class);
        if (method_exists($config, 'clean')) {
            $config->clean();
        }
    }
}
