<?php
/**
 * @category  Mageants MinOrderCustomerGroup
 * @package   Mageants_MinOrderCustomerGroup
 * @copyright Copyright (c) 2023 Mageants
 * @author    Mageants Team <support@mageants.com>
 */
 
namespace Mageants\MinOrderCustomerGroup\Plugin\Quote\Model\Quote\Validator\MinimumOrderAmount;

class ValidationMessage
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_currency;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_productMetadataInterface;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_serialize;

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
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $currency
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Framework\Serialize\Serializer\Json $serialize
     * @param \Magento\Catalog\Model\Product $product
     * @param \Mageants\MinOrderCustomerGroup\Helper\Data $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $currency,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        \Magento\Catalog\Model\ProductFactory $product,
        \Mageants\MinOrderCustomerGroup\Helper\Data $data
    ) {
        $this->_scopeConfig              = $scopeConfig;
        $this->_storeManager             = $storeManager;
        $this->_currency                 = $currency;
        $this->_customerSession          = $customerSession;
        $this->_productMetadataInterface = $productMetadataInterface;
        $this->_session                  = $session;
        $this->_serialize                = $serialize;
        $this->_product                  = $product;
        $this->_helper                   = $data;
    }

    /**
     * Get validation message.
     *
     * @return \Magento\Framework\Phrase
     * @throws \Zend_Currency_Exception
     */
    public function aroundGetMessage()
    {
        $message = $this->_helper->getDiscription();
        
        $currencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        
        $amount = $this->_helper->getAmount();
        
        /* ------ Custom code to set Minimum amount for Customer Group ---- */
       
        /* get current magento version and unserialize data */
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
            $amountArray[$minCustomer['customer_group']] = $minCustomer['minimum_amount'];
            if ($minCustomer['customer_group'] == $currentCusId) {
                $configArray[] = $minCustomer;
            }
        }

        $finalAmount = [];
        $configCompareCat = [];
        $items = $this->_session->getQuote()->getAllVisibleItems();
        foreach ($items as $item) {
            $productId = $item->getProductId();
            $product = $this->_product->create()->load($productId);
            $categoriesIds = $product->getCategoryIds();

            if (isset($categoriesIds) && isset($configArray)) {
                foreach ($categoriesIds as $catId) {
                    foreach ($configArray as $configArrayValue) {
                        // if ($configArrayValue['category'] == $catId) {
                            $configCompareCat = $this->getMinimumAmount($configCompareCat, $catId, $configArrayValue);
                        // }
                    }
                }
            }
        }

        foreach ($configCompareCat as $minimumAmt) {
            $finalAmount[] = $minimumAmt['minimum_amount'];
        }
       
        $minimumAmount = $this->_currency->getCurrency($currencyCode)->toCurrency(min($finalAmount));

        if (!$message) {
            $message = __('Minimum order amount is %1', $minimumAmount);
        } else {
            /* get dynamic message as per customer group and replace with %s to minimum amount */
            $dynamicMsg = sprintf(__($message), $minimumAmount);
            $message = __($dynamicMsg);
        }

        return $message;
    }

    /**
     * Return Minimum Amount
     *
     * @param Array $configCompareCat
     * @param Array $catId
     * @param Array $configArrayValue
     */
    public function getMinimumAmount($configCompareCat, $catId, $configArrayValue)
    {
        if (!empty($configCompareCat) && isset($configCompareCat[$catId])) {
            if ($configArrayValue['minimum_amount'] <= $configCompareCat[$catId]['minimum_amount']) {
                unset($configCompareCat[$catId]);
                $configCompareCat[$catId] = $configArrayValue;
                return $configCompareCat;
            }
        } else {
            $configCompareCat[$catId]  =  $configArrayValue;
            return $configCompareCat;
        }
    }
}
