<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace LR\CustomOptionPricing\Model\ResourceModel\OptionsTierPrice;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Set resource model and determine field mapping
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'LR\CustomOptionPricing\Model\OptionsTierPrice',
            'LR\CustomOptionPricing\Model\ResourceModel\OptionsTierPrice'
        );
    }
}
