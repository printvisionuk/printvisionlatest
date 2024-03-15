<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Ui\Component\Listing\Column;

class Stores extends \Magento\Store\Ui\Component\Listing\Column\Store
{
    /**
     * Get data
     *
     * @param array $item
     */
    protected function prepareItem(array $item)
    {
        if (isset($item[$this->storeKey])) {
            if (!$item[$this->storeKey]) {
                $item[$this->storeKey] = [0];
            }
            if (is_string($item[$this->storeKey])) {
                $item[$this->storeKey] = explode(',', $item[$this->storeKey]);
            }
        }

        return parent::prepareItem($item);
    }
}
