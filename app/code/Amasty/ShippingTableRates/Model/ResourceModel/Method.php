<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\ResourceModel;

/**
 * Shipping Method Resource model
 */
class Method extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const MAIN_TABLE = 'amasty_table_method';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }
}
