<?php
/**
 * @category  Mageants MinOrderCustomerGroup
 * @package   Mageants_MinOrderCustomerGroup
 * @copyright Copyright (c) 2023 Mageants
 * @author    Mageants Team <support@mageants.com>
 */

namespace Mageants\MinOrderCustomerGroup\Plugin\Quote\Model\Quote;

use Magento\Framework\Serialize\SerializerInterface;

class Address
{
    public $checkoutSession;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

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
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Mageants\MinOrderCustomerGroup\Helper\Data
     */
    protected $_helper;

    /**
     * @var TYPE_SHIPPING
     */
    public const TYPE_SHIPPING = 'shipping';

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Framework\Serialize\Serializer\Json $serialize
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Mageants\MinOrderCustomerGroup\Helper\Data $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Checkout\Model\Cart $cart,
        \Mageants\MinOrderCustomerGroup\Helper\Data $data,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_scopeConfig              = $scopeConfig;
        $this->_customerSession          = $customerSession;
        $this->_productMetadataInterface = $productMetadataInterface;
        $this->_session                  = $session;
        $this->_serialize                = $serialize;
        $this->_product                  = $product;
        $this->_cart                     = $cart;
        $this->_helper                   = $data;
        $this->checkoutSession           = $checkoutSession;
    }

    /**
     * Validate minimum amount
     *
     * @param Address $subject
     * @return bool
     */
    public function aroundValidateMinimumAmount(\Magento\Quote\Model\Quote\Address $subject)
    {
        $storeId = $subject->getQuote()->getStoreId();
        $validateEnabled = $this->_helper->isEnabled($storeId);
        $final_Amount = [];

        if (!$validateEnabled) {
            return true;
        }
        $quote = $this->checkoutSession->getQuote();
        $quote->validateMinimumAmount(
            true);
        if (!$subject->getQuote()->getIsVirtual() xor $subject->getAddressType() == self::TYPE_SHIPPING) {
            return true;
        }

        $amount = $this->_helper->getAmountUsingId($storeId);
        
        /*Custom code to set Minimum amount for Customer Group */

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

        $minAmount=[];
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
            $amount = "";
        }

        /*Get Catgory id and product price*/

        /** @var $item \Magento\Quote\Model\Quote\Item */

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

        foreach ($configCompareCat as $minimumAmount) {
            $final_Amount[] = $minimumAmount['minimum_amount'];
        }

        $taxInclude = $this->_helper->getTaxInclude($storeId);

        $includeDiscount = $this->_helper->getIncludeDiscoutAmount($storeId);

        $total = $this->_cart->getQuote()->getSubtotal();
        $taxes = $subject->getBaseTaxAmount();

        if (count($final_Amount) > 1) {
            $final_Amount = min($final_Amount);
        } else {
            $final_Amount = implode(" ", $final_Amount);
        }

        if (empty($final_Amount)) {
            return ($subject->getBaseSubtotalWithDiscount() + $taxes);
        } elseif ($includeDiscount == "1") {
            if ($taxInclude == "1") {
                $subTotal = $this->_cart->getQuote()->getBaseSubtotalWithDiscount();
                return $subTotal + $taxes >= $final_Amount;
            } else {
                $subTotal = $this->_cart->getQuote()->getBaseSubtotalWithDiscount();
                return $subTotal >= $final_Amount;
            }
        } else {
            if ($taxInclude == "1") {
                return $total + $taxes >= $final_Amount;
            } else {
                return $total >= $final_Amount;
            }
        }
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
