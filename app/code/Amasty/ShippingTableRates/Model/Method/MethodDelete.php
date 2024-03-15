<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Model\Method;

use Amasty\ShippingTableRates\Api\Data\MethodInterface;
use Amasty\ShippingTableRates\Api\MethodDeleteInterface;
use Amasty\ShippingTableRates\Model\ResourceModel\Method;
use Magento\Framework\Exception\CouldNotDeleteException;

class MethodDelete implements MethodDeleteInterface
{
    /**
     * @var Method
     */
    private $resource;

    public function __construct(Method $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param MethodInterface $method
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function execute(MethodInterface $method): bool
    {
        try {
            $this->resource->delete($method);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Unable to delete Method'), $exception);
        }

        return true;
    }
}
