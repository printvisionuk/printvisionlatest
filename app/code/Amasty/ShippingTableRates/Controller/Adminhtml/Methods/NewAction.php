<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Controller\Adminhtml\Methods;

use Magento\Framework\Controller\ResultFactory;

/**
 * Create Shipping Method Action
 */
class NewAction extends \Amasty\ShippingTableRates\Controller\Adminhtml\Methods
{
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);

        return $result->forward('edit');
    }
}
