<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Controller\Adminhtml;

/**
 * Abstract class for set correct ACL resource
 */
abstract class Methods extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Amasty_ShippingTableRates::amstrates';

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ShippingTableRates::amstrates');
    }
}
