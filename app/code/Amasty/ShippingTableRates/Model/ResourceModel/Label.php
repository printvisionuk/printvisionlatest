<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\ResourceModel;

/**
 * Method Labels Resource
 */
class Label extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const MAIN_TABLE = 'amasty_method_label';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'entity_id');
    }
}
