<?php
/**
 * @category  Mageants MinOrderCustomerGroup
 * @package   Mageants_MinOrderCustomerGroup
 * @copyright Copyright (c) 2023 Mageants
 * @author    Mageants Team <support@mageants.com>
 */

namespace Mageants\MinOrderCustomerGroup\Plugin\Quote\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class Quote
{
    /**
     * @var Session
     */
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
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var array
     */
    public $productCategory = [];

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Framework\Serialize\Serializer\Json $serialize
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Mageants\MinOrderCustomerGroup\Helper\Data $data
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CollectionFactory $categoryCollectionFactory
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
        \Magento\Checkout\Model\Session $checkoutSession,
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $categoryCollectionFactory
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
        $this->categoryRepository        = $categoryRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Validate minimum amount
     *
     * @param \Magento\Quote\Model\Quote $subject
     * @param callable $proceed
     * @param boolean $multishipping
     * @return void
     */
    public function aroundValidateMinimumAmount(
        \Magento\Quote\Model\Quote $subject,
        callable $proceed,
        $multishipping = false
    ) {
        $priceByCat = $this->getPricesByCategorySeted();
        $storeId = $subject->getStoreId();
        $minOrderActive = $this->_helper->isEnabled($storeId);
        if (!$minOrderActive) {
            return true;
        }
        $includeDiscount = $this->_helper->getIncludeDiscoutAmount($storeId);
        $minOrderMulti = $this->_helper->getMultiAddressEnable($storeId);
        $taxInclude = $this->_helper->getTaxInclude($storeId);

        $addresses = $subject->getAllAddresses();

        if (!$multishipping) {
            foreach ($addresses as $address) {
                /* @var $address Address */
                if (!$address->validateMinimumAmount()) {
                    return false;
                }
            }
            return true;
        }

        $amount = $this->_helper->getAmountUsingId($storeId);
        $minArray = $this->_serialize->unserialize($amount);
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
            $productFinalPrice = $product->getFinalPrice();
            // $categoriesIds = $product->getCategoryIds();

            // if (isset($categoriesIds) && isset($configArray)) {
            //     foreach ($categoriesIds as $catId) {
            //         foreach ($configArray as $configArrayValue) {
            //             if ($configArrayValue['category'] == '0' || $configArrayValue['category'] == $catId) {
            //                 $configCompareCat = $this->getMinimumAmount(
            //                     $configCompareCat,
            //                     $configArrayValue['category'],
            //                     $configArrayValue
            //                 );
            //             }
            //         }
            //     }
            // }
        }

        if ($minOrderMulti) {
            foreach ($configCompareCat as $minimumAmount) {
                // $ruleCategoryID = $minimumAmount['category'];
                $ruleCustomerGroup = $minimumAmount['customer_group'];
                $ruleMinAmount = $minimumAmount['minimum_amount'];
    
                foreach ($priceByCat as $cat_ID => $totalCatPrice) {
                    if (($ruleMinAmount > $totalCatPrice)) {
                        return false;
                    }
                }
            }
        } else {
            return false;
        }
        return true;
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

    /**
     * Get Prices By Category
     *
     * @return void
     */
    public function getPricesByCategorySeted()
    {
        $quote = $this->_session->getQuote();
        $pricesByCategory = [];
        $cartSubtotal = 0;

        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $categories = $product->getCategoryIds();
            $price = $item->getPrice();
            $quantity = $item->getQty();
            $cartSubtotal += $price * $quantity;

            foreach ($categories as $categoryId) {
                if (!isset($pricesByCategory[$categoryId])) {
                    $pricesByCategory[$categoryId] = 0;
                }
                $pricesByCategory[$categoryId] += $price * $quantity;
            }
        }
        $pricesByCategory[0] = $cartSubtotal;
        return $pricesByCategory;
    }
}
