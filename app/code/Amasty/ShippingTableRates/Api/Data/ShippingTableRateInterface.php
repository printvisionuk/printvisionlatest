<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Api\Data;

interface ShippingTableRateInterface
{
    /**
     * Constants defined for keys of data array
     */
    public const ID = 'id';
    public const COUNTRY = 'country';
    public const STATE = 'state';
    public const ZIP_FROM = 'zip_from';
    public const ZIP_TO = 'zip_to';
    public const PRICE_FROM = 'price_from';
    public const PRICE_TO = 'price_to';
    public const WEIGHT_FROM = 'weight_from';
    public const WEIGHT_TO = 'weight_to';
    public const QTY_FROM = 'qty_from';
    public const QTY_TO = 'qty_to';
    public const SHIPPING_TYPE = 'shipping_type';
    public const COST_BASE = 'cost_base';
    public const COST_PERCENT = 'cost_percent';
    public const COST_PRODUCT = 'cost_product';
    public const COST_WEIGHT = 'cost_weight';
    public const START_WEIGHT = 'start_weight';
    public const TIME_DELIVERY = 'time_delivery';
    public const NUM_ZIP_FROM = 'num_zip_from';
    public const NUM_ZIP_TO = 'num_zip_to';
    public const NAME_DELIVERY = 'name_delivery';
    public const UNIT_WEIGHT_CONVERSION = 'unit_weight_conversion';
    public const WEIGHT_ROUNDING = 'weight_rounding';
    public const CITY = 'city';
    public const METHOD_ID = 'method_id';

    /**
     * @return null|string
     */
    public function getCountry();

    /**
     * @param null|string $country
     *
     * @return ShippingTableRateInterface
     */
    public function setCountry($country);

    /**
     * @return null|string
     */
    public function getState();

    /**
     * @param null|string $state
     *
     * @return ShippingTableRateInterface
     */
    public function setState($state);

    /**
     * @return null|string
     */
    public function getZipFrom();

    /**
     * @param null|string $zipFrom
     *
     * @return ShippingTableRateInterface
     */
    public function setZipFrom($zipFrom);

    /**
     * @return null|string
     */
    public function getZipTo();

    /**
     * @param null|string $zipTo
     *
     * @return ShippingTableRateInterface
     */
    public function setZipTo($zipTo);

    /**
     * @return null|string|float
     */
    public function getPriceFrom();

    /**
     * @param null|string|float $priceFrom
     *
     * @return ShippingTableRateInterface
     */
    public function setPriceFrom($priceFrom);

    /**
     * @return null|string|float
     */
    public function getPriceTo();

    /**
     * @param null|string|float $priceTo
     *
     * @return ShippingTableRateInterface
     */
    public function setPriceTo($priceTo);

    /**
     * @return null|string|float
     */
    public function getWeightFrom();

    /**
     * @param null|string|float $weightFrom
     *
     * @return ShippingTableRateInterface
     */
    public function setWeightFrom($weightFrom);

    /**
     * @return null|string|float
     */
    public function getWeightTo();

    /**
     * @param null|string|float $weightTo
     *
     * @return ShippingTableRateInterface
     */
    public function setWeightTo($weightTo);

    /**
     * @return null|string|float
     */
    public function getQtyFrom();

    /**
     * @param null|string|float $qtyFrom
     *
     * @return ShippingTableRateInterface
     */
    public function setQtyFrom($qtyFrom);

    /**
     * @return null|string|float
     */
    public function getQtyTo();

    /**
     * @param null|string|float $qtyTo
     *
     * @return ShippingTableRateInterface
     */
    public function setQtyTo($qtyTo);

    /**
     * @return null|string
     */
    public function getShippingType();

    /**
     * @param null|string $shippingType
     *
     * @return ShippingTableRateInterface
     */
    public function setShippingType($shippingType);

    /**
     * @return string|float
     */
    public function getCostBase();

    /**
     * @param string|float $costBase
     *
     * @return ShippingTableRateInterface
     */
    public function setCostBase($costBase);

    /**
     * @return null|string|float
     */
    public function getCostPercent();

    /**
     * @param null|string|float $costPercent
     *
     * @return ShippingTableRateInterface
     */
    public function setCostPercent($costPercent);

    /**
     * @return null|string|float
     */
    public function getCostProduct();

    /**
     * @param null|string|float $costProduct
     *
     * @return ShippingTableRateInterface
     */
    public function setCostProduct($costProduct);

    /**
     * @return null|string|float
     */
    public function getCostWeight();

    /**
     * @param null|string|float $costWeight
     *
     * @return ShippingTableRateInterface
     */
    public function setCostWeight($costWeight);

    /**
     * @return null|string
     */
    public function getTimeDelivery();

    /**
     * @param null|string $timeDelivery
     *
     * @return ShippingTableRateInterface
     */
    public function setTimeDelivery($timeDelivery);

    /**
     * @return null|string
     */
    public function getCity();

    /**
     * @param null|string $city
     *
     * @return ShippingTableRateInterface
     */
    public function setCity($city);

    /**
     * @return null|string|int
     */
    public function getMethodId();

    /**
     * @param null|string|int $shippingMethodId
     *
     * @return ShippingTableRateInterface
     */
    public function setMethodId($shippingMethodId);

    /**
     * @return float
     */
    public function getStartWeight(): float;

    /**
     * @param float $weight
     */
    public function setStartWeight(float $weight): void;

    /**
     * @return float
     */
    public function getUnitWeightConversion(): float;

    /**
     * @param float $conversionRate
     */
    public function setUnitWeightConversion(float $conversionRate): void;

    /**
     * @return int
     */
    public function getWeightRounding(): int;

    /**
     * @param int $option
     */
    public function setWeightRounding(int $option): void;
}
