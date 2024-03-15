<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Config\Source;

/**
 * Shipping Behavior options for Bundle Products
 */
class Bundle implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $vals = [
            '0' => __('As in "Ship Bundle Items" setting'),
            '1' => __('From bundle product'),
            '2' => __('From items in bundle'),
        ];

        $options = [];
        foreach ($vals as $k => $v) {
            $options[] = [
                'value' => $k,
                'label' => $v
            ];
        }

        return $options;
    }
}
