<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Source\Option;

use Magento\Framework\Data\OptionSourceInterface;

class WeightRoundingOptions implements OptionSourceInterface
{
    public const NONE = 0;
    public const UP = 1;
    public const DOWN = 2;

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::NONE, 'label' => 'None'],
            ['value' => self::UP, 'label' => 'Up'],
            ['value' => self::DOWN, 'label' => 'Down']
        ];
    }
}
