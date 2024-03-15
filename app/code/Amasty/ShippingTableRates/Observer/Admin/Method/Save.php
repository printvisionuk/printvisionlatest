<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Observer\Admin\Method;

use Amasty\ShippingTableRates\Model\ResourceModel\Rate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Save implements ObserverInterface
{
    /**
     * @var Rate
     */
    private $rate;

    public function __construct(Rate $rate)
    {
        $this->rate = $rate;
    }

    /**
     * Event name 'amasty_shipping_table_rates_method_save_after'
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $method = $observer->getObject();

        if ($method->getIsDuplicateMethod()) {
            $originalId = (int)$method->getOrigData()['id'];
            $this->rate->batchDuplicateInsertions((int)$method->getId(), $originalId);
        }
    }
}
