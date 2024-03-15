<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Source\Option;

use Amasty\ShippingTableRates\Helper\Data;
use Magento\Framework\Data\OptionSourceInterface;

class ShippingTypeOptions implements OptionSourceInterface
{
    public const ALL_SHIPPING_TYPES = 0;

    /**
     * @var Data
     */
    private $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [
            ['value' => self::ALL_SHIPPING_TYPES, 'label' => __('All')]
        ];

        return array_merge($options, $this->helper->getTypes());
    }
}
