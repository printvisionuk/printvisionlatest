<?php
/**
 * Copyright © 2020 Design'N'Buy (inquiry@designnbuy.com). All rights reserved.
 * 
 */
namespace Designnbuy\CustomOptionPlugin\Block;

/**
 * Class Attachment
 * @package Designnbuy\Productattach\Block
 */
class Pricecalculate extends \Magento\Framework\View\Element\Template
{

    protected $scopeConfig;

    protected $_helper;

    protected $_coreRegistry = null;

    protected $session;

    protected $baseUnit;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\Currency $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeInterface,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->localeCurrency = $localeCurrency;
        $this->storeInterface = $storeInterface;
        $this->session = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */

    public function getCurrentProduct() {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getStoreCurrencySymbol()
    {
        return $this->localeCurrency->getCurrencySymbol();
    }

    // Code By Vips starts
    public function getCustomerGroupId()
    {
        $customerGroupId = 0;
        if($this->session->isLoggedIn()) {
            $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        }
        return $customerGroupId;
    }

    public function getGroupBaseSquareAreaPricing($groupId)
    {
        $currentProductSAP = $this->getCurrentProduct()->getSquareAreaPricing();
        if($currentProductSAP) {
            $priceDataDecode = json_decode($currentProductSAP,true);
            
            $sortedPriceDataDecode = [];
            foreach ($priceDataDecode as $key => $sapObj) {
                if(isset($sapObj['group_id']) && $sapObj['group_id'] == $groupId)   {
                    // unset($priceDataDecode[$key]);
                    $sortedPriceDataDecode[$sapObj['square_area']] = $sapObj;
                }
            }
            ksort($sortedPriceDataDecode);
            return json_encode(array_values($sortedPriceDataDecode));
        }
        return json_encode([]);
    }
    // Code By Vips ends

}
