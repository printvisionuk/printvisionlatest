<?php
/**
 * @category  Mageants MinOrderCustomerGroup
 * @package   Mageants_MinOrderCustomerGroup
 * @copyright Copyright (c) 2023 Mageants
 * @author    Mageants Team <support@mageants.com>
 */

namespace Mageants\MinOrderCustomerGroup\Observer;

class Orderplacebefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Quote\Model\Quote\Address
     */
    protected $_address;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManagerInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfigInterface;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_productMetadataInterface;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_serialize;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_currencyInterface;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Mageants\MinOrderCustomerGroup\Helper\Data
     */
    protected $_helper;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface
     * @param \Magento\Framework\Serialize\Serializer\Json $serialize
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Locale\CurrencyInterface $currencyInterface
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Catalog\Model\Product $product
     * @param \Mageants\MinOrderCustomerGroup\Helper\Data $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Model\Quote\Address $address,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Locale\CurrencyInterface $currencyInterface,
        \Magento\Checkout\Model\Session $session,
        \Magento\Catalog\Model\ProductFactory $product,
        \Mageants\MinOrderCustomerGroup\Helper\Data $data
    ) {
        $this->_customerSession            = $customerSession;
        $this->_address                    = $address;
        $this->_storeManagerInterface      = $storeManagerInterface;
        $this->_scopeConfigInterface       = $scopeConfigInterface;
        $this->_productMetadataInterface   = $productMetadataInterface;
        $this->_serialize                  = $serialize;
        $this->_cart                       = $cart;
        $this->_currencyInterface          = $currencyInterface;
        $this->_session                    = $session;
        $this->_product                    = $product;
        $this->_helper                     = $data;
    }

    /**
     * Below is the method that will fire whenever the event runs!
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $this->_storeManagerInterface->getStore()->getStoreId();

        $minimumOrderActive = $this->_helper->isSetActive();

        $amount = $this->_helper->getAmountUsingId($storeId);

        $message = $this->_helper->getDiscription();

        $includeDiscount = $this->_helper->getIncludeDiscoutAmount($storeId);

        $taxInclude = $this->_helper->getTaxInclude($storeId);
        
        if ($minimumOrderActive == true) {
            $magentoVersion = $this->_productMetadataInterface->getVersion();
            if (version_compare($magentoVersion, '2.2.0') < 0) {
                $minArray = $this->_serialize->unserialize($amount);
            } else {
                $minArray = $this->_serialize->unserialize($amount);
            }
           
            if ($this->_customerSession->isLoggedIn()) {
                $currentCusId = $this->_customerSession->getCustomer()->getGroupId();
            } else {
                $currentCusId = 0;
            }

            $configArray = [];
            foreach ($minArray as $minCustomer) {
                $minAmount[$minCustomer['customer_group']] = $minCustomer['minimum_amount'];
                if ($minCustomer['customer_group'] == $currentCusId) {
                    $configArray[] = $minCustomer;
                }
            }
           
            if (array_key_exists($currentCusId, $minAmount)) {
                $amount = $minAmount[$currentCusId];
            } else {
                $amount = null;
            }
            $subTotal = $this->_cart->getQuote()->getSubtotalWithDiscount();
            $total = $this->_cart->getQuote()->getSubtotal();
            $grandTotal = $this->_cart->getQuote()->getGrandTotal();

            $currencyCode = $this->_storeManagerInterface->getStore()->getCurrentCurrencyCode();
            $minimumAmount = $this->_currencyInterface->getCurrency($currencyCode)->toCurrency($amount);
           
            if ($includeDiscount == "1") {
                if ($taxInclude == "1") {
                    $configCompareCat = [];
                    $items = $this->_session->getQuote()->getAllItems();
                    foreach ($items as $item) {
                        return $this->checkAmountWithGrandTotal($item, $configArray, $grandTotal, $amount, $message);
                    }
                } else {
                    $configCompareCat = [];
                    $items = $this->_session->getQuote()->getAllItems();
                    foreach ($items as $item) {
                        return $this->checkAmountWithSubTotal($item, $configArray, $subTotal, $amount, $message);
                    }
                }
            } else {
                if ($taxInclude == "1") {
                    $configCompareCat = [];
                    $items = $this->_session->getQuote()->getAllItems();
                    foreach ($items as $item) {
                        return $this->checkAmountWithGrandTotal($item, $configArray, $grandTotal, $amount, $message);
                    }
                } else {
                    $configCompareCat = [];
                    $items = $this->_session->getQuote()->getAllItems();
                    foreach ($items as $item) {
                        return checkAmountWithTotal($item, $configArray, $total, $amount, $message);
                    }
                }
            }
        }
    }

    /**
     * Check Minimum amount with GrandTotal
     *
     * @param Array  $item
     * @param Array  $configArray
     * @param int    $grandTotal
     * @param int    $amount
     * @param String $message
     */
    public function checkAmountWithGrandTotal($item, $configArray, $grandTotal, $amount, $message)
    {
        $productId = $item->getProductId();
        $product = $this->_product->load($productId);
        $categoriesIds = $product->getCategoryIds();

        foreach ($categoriesIds as $catId) {
            foreach ($configArray as $configArrayValue) {
                // if ($configArrayValue['category'] == $catId) {
                    if ($grandTotal <= $amount) {
                        if ($message == null) {
                            $staticMsg = "Minimum order amount is ".$minimumAmount;
                            throw new \Magento\Framework\Exception\LocalizedException(__($staticMsg));
                        } else {
                            $dynamicMsg = sprintf(__($message), $minimumAmount);
                            throw new \Magento\Framework\Exception\LocalizedException(__($dynamicMsg));
                        }
                    } else {
                        return true;
                    }
                // }
            }
        }
    }

    /**
     * Check Minimum amount with SubTotal(SubtotalWithDiscount)
     *
     * @param Array  $item
     * @param Array  $configArray
     * @param int    $subTotal
     * @param int    $amount
     * @param String $message
     */
    public function checkAmountWithSubTotal($item, $configArray, $subTotal, $amount, $message)
    {
        $productId = $item->getProductId();
        $product = $this->_product->load($productId);
        $categoriesIds = $product->getCategoryIds();

        foreach ($categoriesIds as $catId) {
            foreach ($configArray as $configArrayValue) {
                // if ($configArrayValue['category'] == $catId) {
                    if ($subTotal <= $amount) {
                        if ($message == null) {
                            $staticMsg = "Minimum order amount is ".$minimumAmount;
                            throw new \Magento\Framework\Exception\LocalizedException(__($staticMsg));
                        } else {
                            $dynamicMsg = sprintf(__($message), $minimumAmount);
                            throw new \Magento\Framework\Exception\LocalizedException(__($dynamicMsg));
                        }
                    } else {
                        return true;
                    }
                // }
            }
        }
    }

    /**
     * Check Minimum amount with Total(SubTotal)
     *
     * @param Array  $item
     * @param Array  $configArray
     * @param int    $total
     * @param int    $amount
     * @param String $message
     */
    public function checkAmountWithTotal($item, $configArray, $total, $amount, $message)
    {
        $productId = $item->getProductId();
        $product = $this->_product->load($productId);
        $categoriesIds = $product->getCategoryIds();

        foreach ($categoriesIds as $catId) {
            foreach ($configArray as $configArrayValue) {
                // if ($configArrayValue['category'] == $catId) {
                    if ($total <= $amount) {
                        if ($message == null) {
                            $staticMsg = "Minimum order amount is ".$minimumAmount;
                            throw new \Magento\Framework\Exception\LocalizedException(__($staticMsg));
                        } else {
                            $dynamicMsg = sprintf(__($message), $minimumAmount);
                            throw new \Magento\Framework\Exception\LocalizedException(__($dynamicMsg));
                        }
                    } else {
                        return true;
                    }
                // }
            }
        }
    }
}
