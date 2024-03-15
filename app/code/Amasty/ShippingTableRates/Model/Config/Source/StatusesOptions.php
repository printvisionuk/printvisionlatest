<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class StatusesOptions implements OptionSourceInterface
{
    public const INACTIVE = 0;
    public const ACTIVE = 1;

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::INACTIVE,
                'label' => __('Inactive')
            ],
            [
                'value' => self::ACTIVE,
                'label' => __('Active')
            ],
        ];
    }
}
