<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\OptionProvider\Provider;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store;

class StoresOptionsProvider implements OptionSourceInterface
{
    /**
     * @var Store
     */
    private $store;

    /**
     * @param Store $store
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return $this->store->getStoreValuesForForm(false, true);
    }
}
