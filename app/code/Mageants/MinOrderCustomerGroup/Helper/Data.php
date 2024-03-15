<?php
/**
 * @category  Mageants MinOrderCustomerGroup
 * @package   Mageants_MinOrderCustomerGroup
 * @copyright Copyright (c) 2023 Mageants
 * @author    Mageants Team <support@mageants.com>
 */

namespace Mageants\MinOrderCustomerGroup\Helper;

use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * Return Boolean
     *
     * @param int $storeId
     */
    public function isEnabled($storeId)
    {
        return $this->_scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return Amount
     *
     * @param int $storeId
     */
    public function getAmountUsingId($storeId)
    {
        return $this->_scopeConfig->getValue(
            'sales/minimum_order/amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return Include Tax Amount
     *
     * @param int $storeId
     */
    public function getTaxInclude($storeId)
    {
        return $this->_scopeConfig->getValue(
            'sales/minimum_order/tax_including',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return Include Discount Amount
     *
     * @param int $storeId
     */
    public function getIncludeDiscoutAmount($storeId)
    {
        return $this->_scopeConfig->getValue(
            'sales/minimum_order/include_discount_amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return MultiAddress is Enable
     *
     * @param int $storeId
     * @return void
     */
    public function getMultiAddressEnable($storeId)
    {
        return $this->_scopeConfig->getValue(
            'sales/minimum_order/multi_address',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return Amount
     */
    public function getAmount()
    {
        return $this->_scopeConfig->getValue(
            'sales/minimum_order/amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return Amount
     */
    public function getDiscription()
    {
        return $this->_scopeConfig->getValue(
            'sales/minimum_order/description',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return Boolean
     */
    public function isSetActive()
    {
        return $this->_scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
