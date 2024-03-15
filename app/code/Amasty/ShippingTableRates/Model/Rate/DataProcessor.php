<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Rate;

use Amasty\ShippingTableRates\Helper\Data;

class DataProcessor
{
    /**
     * @var Data
     */
    private $helperSTR;

    public function __construct(Data $helperSTR)
    {
        $this->helperSTR = $helperSTR;
    }

    public function process(array $rateData): array
    {
        $fullZipFrom = $this->helperSTR->getDataFromZip($rateData['zip_from']);
        $fullZipTo = $this->helperSTR->getDataFromZip($rateData['zip_to']);
        $rateData['num_zip_from'] = $fullZipFrom['district'];
        $rateData['num_zip_to'] = $fullZipTo['district'];

        return $rateData;
    }
}
