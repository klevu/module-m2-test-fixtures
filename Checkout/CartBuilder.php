<?php

declare(strict_types=1);

namespace Klevu\TestFixtures\Checkout;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\SessionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Attribute;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResourceModel;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

class CartBuilder
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;
    /**
     * @var CartInterface
     */
    private CartInterface $cart;
    /**
     * @var Session
     */
    private Session $session;
    /**
     * @var DataObject[][] Array in the form [sku => [buyRequest]] (multiple requests per sku are possible)
     */
    private array $addToCartRequests;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SessionFactory $checkoutSessionFactory
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SessionFactory $checkoutSessionFactory,
    ) {
        $this->productRepository = $productRepository;
        $this->session = $checkoutSessionFactory->create();
        $this->cart = $this->session->getQuote();
        $this->addToCartRequests = [];
    }

    /**
     * @return CartBuilder
     */
    public static function forCurrentSession(): CartBuilder
    {
        $objectManager = Bootstrap::getObjectManager();

        $result = new static(
            $objectManager->create(ProductRepositoryInterface::class),
            $objectManager->create(SessionFactory::class),
        );
        $result->cart->setStoreId(1);
        $result->cart->setIsMultiShipping(0);
        $result->cart->setIsActive(true);
        $result->cart->setCheckoutMethod('guest');
        $result->cart->setDataUsingMethod('email', 'no-reply@klevu.com');

        return $result;
    }

    /**
     * @param int|null $storeId
     *
     * @return $this
     */
    public function withStoreId(?int $storeId = null): CartBuilder
    {
        $result = clone $this;
        $result->cart->setStoreId($storeId);

        return $result;
    }

    /**
     * @param int|null $reservedOrderId
     *
     * @return $this
     */
    public function withReservedOrderId(?int $reservedOrderId = null): CartBuilder
    {
        $result = clone $this;
        $result->cart->setReservedOrderId(
            $reservedOrderId
                ?: random_int(1000000000000, 9999999999999),
        );

        return $result;
    }

    /**
     * @param mixed[]|null $address
     *
     * @return $this
     */
    public function withAddress(?array $address = []): CartBuilder
    {
        $customerAddress = [
            'prefix' => $address['prefix'] ?? '',
            'firstname' => $address['firstname'] ?? 'John',
            'middlename' => $address['middlename'] ?? '',
            'lastname' => $address['lastname'] ?? 'Smith',
            'suffix' => $address['suffix'] ?? '',
            'company' => $address['company'] ?? '',
            'street' => [
                '0' => $address['street1'] ?? '',
                '1' => $address['street2'] ?? '',
            ],
            'city' => $address['city'] ?? 'London',
            'country_id' => $address['country_id'] ?? 'GB',
            'region' => $address['region'] ?? '',
            'postcode' => $address['postcode'] ?? 'SW19 1AB',
            'telephone' => $address['telephone'] ?? '0123456789',
            'fax' => $address['fax'] ?? '',
            'vat_id' => $address['vat_id'] ?? '',
            'save_in_address_book' => 1,
        ];

        $result = clone $this;
        $result->cart->setCustomerFirstname($address['firstname'] ?? 'John');
        $result->cart->setCustomerLastname($address['lastname'] ?? 'Smith');
        $result->cart->setCustomerEmail($address['email'] ?? 'customer@klevu.com');
        $result->cart->getBillingAddress()->addData($customerAddress);
        $shippingAddress = $result->cart->getShippingAddress();
        $shippingAddress->addData($customerAddress);
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($address['shipping_method'] ?? 'flatrate_flatrate');

        return $result;
    }

    /**
     * @param string $sku
     * @param float $qty
     *
     * @return $this
     */
    public function withSimpleProduct(string $sku, float $qty = 1): CartBuilder
    {
        $result = clone $this;
        $result->addToCartRequests[$sku][] = new DataObject(['qty' => $qty]);

        return $result;
    }

    /**
     * @param string $sku
     * @param mixed[] $options
     * @param float $qty
     *
     * @return $this
     */
    public function withConfigurableProduct(
        string $sku,
        array $options,
        float $qty = 1.0,
    ): CartBuilder {
        return $this->withProductRequest(
            sku: $sku,
            qty: $qty,
            request: [
                'options' => $options,
            ],
        );
    }

    /**
     * @param string $sku
     * @param mixed[] $options
     * @param float $qty
     *
     * @return $this
     */
    public function withGroupedProduct(
        string $sku,
        array $options,
        float $qty = 1.0,
    ): CartBuilder {
        return $this->withProductRequest(
            sku: $sku,
            qty: $qty,
            request: [
                'options' => $options,
            ],
        );
    }

    /**
     * Lower-level API to support arbitrary products
     *
     * @param string $sku
     * @param float|int $qty
     * @param mixed[] $request
     *
     * @return CartBuilder
     */
    public function withProductRequest(string $sku, float|int $qty = 1, array $request = []): CartBuilder
    {
        $result = clone $this;
        $requestInfo = array_merge(['qty' => (float)$qty], $request);
        $result->addToCartRequests[$sku][] = new DataObject($requestInfo);

        return $result;
    }

    /**
     * @return CartInterface
     * @throws LocalizedException
     */
    public function build(): CartInterface
    {
        $objectManager = ObjectManager::getInstance();
        foreach ($this->addToCartRequests as $sku => $requests) {
            /** @var Product $product */
            $product = $this->productRepository->get($sku);

            // @todo Remove and resolve stock issues with configurables
            $objectManager->get(ProductHelper::class)->setSkipSaleableCheck(true);

            foreach ($requests as $requestInfo) {
                switch ($product->getTypeId()) {
                    case Grouped::TYPE_CODE:
                        $requestInfo = $this->buildGroupedProductRequestInfo($requestInfo, $product);
                        break;

                    case Configurable::TYPE_CODE:
                        $requestInfo = $this->buildConfigurableProductRequestInfo($requestInfo, $product);
                        break;
                }

                $this->cart->addProduct(
                    product: $product,
                    request: $requestInfo,
                );
            }
        }
        $this->cart->collectTotals();

        $quoteResourceModel = $objectManager->get(QuoteResourceModel::class);
        $quoteResourceModel->save($this->cart);

        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $objectManager->create(QuoteIdMaskFactory::class)->create();
        $quoteIdMask->setDataUsingMethod('quote_id', $this->cart->getId());
        $quoteIdMask->setDataChanges(true);
        $quoteIdMaskResourceModel = $objectManager->get(QuoteResourceModel\QuoteIdMask::class);
        $quoteIdMaskResourceModel->save($quoteIdMask);

        $this->session->replaceQuote($this->cart);
        $this->session->unsLastRealOrderId();

        return $this->cart;
    }

    /**
     * @param mixed $requestInfo
     * @param Product $product
     *
     * @return mixed
     */
    private function buildGroupedProductRequestInfo(mixed $requestInfo, Product $product): mixed
    {
        $requestOptions = $requestInfo->getData('options')
            ?: [];
        $requestInfo->unsetData('options');

        /** @var Grouped $typeInstance */
        $typeInstance = $product->getTypeInstance();
        /** @var ProductInterface[] $associatedProducts */
        $associatedProducts = $typeInstance->getAssociatedProducts($product);

        $requestInfo->setData('product', $product->getId());
        $requestInfo->setData('item', $product->getId());
        // @todo Replace with child id => qty
        $superGroup = [];
        foreach ($requestOptions as $associatedSku => $qtyOrdered) {
            /** @var ProductInterface $childProduct */
            $childProduct = current(array_filter(
                $associatedProducts,
                static fn (ProductInterface $associatedProduct): bool => (
                    $associatedSku === $associatedProduct->getSku()
                ),
            ));
            if (!$childProduct) {
                continue;
            }
            $superGroup[(int)$childProduct->getId()] = $qtyOrdered;
        }
        $requestInfo->setData('super_group', $superGroup);

        return $requestInfo;
    }

    /**
     * @param mixed $requestInfo
     * @param Product $product
     *
     * @return mixed
     */
    private function buildConfigurableProductRequestInfo(mixed $requestInfo, Product $product): mixed
    {
        $requestOptions = $requestInfo->getData('options')
            ?: [];
        $requestInfo->unsetData('options');
        $requestInfo->setData('product', $product->getId());

        $superAttribute = [];
        /** @var Configurable $typeInstance */
        $typeInstance = $product->getTypeInstance();
        $configurableAttributes = $typeInstance->getConfigurableAttributesAsArray($product);
        foreach ($requestOptions as $attributeCode => $optionId) {
            /** @var Attribute $configurableAttribute */
            $configurableAttribute = current(array_filter(
                $configurableAttributes,
                static fn (array $attribute): bool => ($attributeCode === $attribute['attribute_code']),
            ));
            if (!$configurableAttribute) {
                continue;
            }
            $superAttribute[$configurableAttribute['attribute_id']] = $optionId;
        }
        $requestInfo->setData('super_attribute', $superAttribute);

        return $requestInfo;
    }
}
