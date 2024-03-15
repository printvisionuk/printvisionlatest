<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Import\Rate;

use Amasty\Base\Model\Import\Mapping\Mapping as MappingBase;
use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\Base\Model\Import\Mapping\MappingInterface;

class Mapping extends MappingBase implements MappingInterface
{
    /**
     * Constants defined default values for empty variables
     */
    public const COUNTRY_CODE_ALL = 0;
    public const STATE_CODE_ALL = 0;
    public const SHIPPING_TYPE_ALL = 0;

    /**
     * List of numeric variables
     */
    public const NUMERIC_DATA = [
        ShippingTableRateInterface::PRICE_FROM,
        ShippingTableRateInterface::PRICE_TO,
        ShippingTableRateInterface::WEIGHT_FROM,
        ShippingTableRateInterface::WEIGHT_TO,
        ShippingTableRateInterface::QTY_FROM,
        ShippingTableRateInterface::QTY_TO,
        ShippingTableRateInterface::COST_BASE,
        ShippingTableRateInterface::COST_PERCENT,
        ShippingTableRateInterface::COST_PRODUCT,
        ShippingTableRateInterface::START_WEIGHT,
        ShippingTableRateInterface::COST_WEIGHT,
        ShippingTableRateInterface::UNIT_WEIGHT_CONVERSION
    ];

    /**
     * List of descriptions for variables
     */
    public const DESCRIPTION_DATA = [
        ShippingTableRateInterface::COST_BASE => 'rate' . ' ' . '(Base Rate for the Order)',
        ShippingTableRateInterface::COST_PERCENT => 'ppp' . ' ' . '(Percentage per Product)',
        ShippingTableRateInterface::COST_PRODUCT => 'frpp' . ' ' . '(Fixed Rate per Product)',
        ShippingTableRateInterface::COST_WEIGHT => 'frpuw' . ' ' . '(Fixed Rate per 1 unit of weight)'
    ];

    /**
     * @var array
     */
    protected $mappings = [
        'country' => ShippingTableRateInterface::COUNTRY,
        'state' => ShippingTableRateInterface::STATE,
        'city' => ShippingTableRateInterface::CITY,
        'zip_from' => ShippingTableRateInterface::ZIP_FROM,
        'zip_to' => ShippingTableRateInterface::ZIP_TO,
        'price_from' => ShippingTableRateInterface::PRICE_FROM,
        'price_to' => ShippingTableRateInterface::PRICE_TO,
        'weight_from' => ShippingTableRateInterface::WEIGHT_FROM,
        'weight_to' => ShippingTableRateInterface::WEIGHT_TO,
        'shipping_type' => ShippingTableRateInterface::SHIPPING_TYPE,
        'rate' => ShippingTableRateInterface::COST_BASE,
        'ppp' => ShippingTableRateInterface::COST_PERCENT,
        'frpp' => ShippingTableRateInterface::COST_PRODUCT,
        'frpuw' => ShippingTableRateInterface::COST_WEIGHT,
        'estimated_delivery' => ShippingTableRateInterface::TIME_DELIVERY,
        'name_delivery' => ShippingTableRateInterface::NAME_DELIVERY,
        'qty_from' => ShippingTableRateInterface::QTY_FROM,
        'start_weight' => ShippingTableRateInterface::START_WEIGHT,
        'qty_to' => ShippingTableRateInterface::QTY_TO,
        'unit_weight_conversion' => ShippingTableRateInterface::UNIT_WEIGHT_CONVERSION,
        'weight_rounding' => ShippingTableRateInterface::WEIGHT_ROUNDING,
        'source' => 'source'
    ];

    /**
     * @var string
     */
    protected $masterAttributeCode = 'rate';
}
