<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace LR\CustomOptionPricing\Model\ResourceModel;

use LR\CustomOptionPricing\Model\OptionsTierPrice as OptionsTierPriceModel;


class OptionsTierPrice extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            OptionsTierPriceModel::TABLE_NAME,
            OptionsTierPriceModel::COLUMN_OPTION_TYPE_TIER_PRICE_ID
        );
    }
}
