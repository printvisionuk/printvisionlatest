<?php

namespace LR\Extra\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public const GET_PRODUCT_TAB_VALUE = 'catalog/frontend/product_tab';

    protected $scopeConfig;
 
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getProductTabValue()
    {
        $productTabValue = $this->scopeConfig->getValue(self::GET_PRODUCT_TAB_VALUE,ScopeInterface::SCOPE_STORE);
        return $productTabValue;
    }
}
