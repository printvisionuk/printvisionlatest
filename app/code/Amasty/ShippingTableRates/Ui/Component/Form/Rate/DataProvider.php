<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Ui\Component\Form\Rate;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Model\MethodIdContainer;
use Amasty\ShippingTableRates\Model\Rate;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var MethodIdContainer
     */
    private $methodIdContainer;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        RequestInterface $request,
        MethodIdContainer $methodIdContainer,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->methodIdContainer = $methodIdContainer;
        $this->collection = $collectionFactory->create();
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $result = [];
        $id = (int)$this->request->getParam(ShippingTableRateInterface::ID);

        if ($id) {
            $this->prepareCollection($id);
            $result[$id] = $this->collection->getFirstItem()->getData();
        } else {
            $result[null] = $this->getDefaultData();
        }

        return $result;
    }

    /**
     * @param int $id
     */
    public function prepareCollection(int $id): void
    {
        $this->collection = $this->collectionFactory->create();
        $this->collection->addFieldToFilter('main_table.'.ShippingTableRateInterface::ID, $id);
    }

    /**
     * @return array
     */
    private function getDefaultData(): array
    {
        return [
            ShippingTableRateInterface::METHOD_ID => $this->methodIdContainer->getMethodId(),
            ShippingTableRateInterface::WEIGHT_FROM => 0,
            ShippingTableRateInterface::QTY_FROM => 0,
            ShippingTableRateInterface::PRICE_FROM => 0,
            ShippingTableRateInterface::WEIGHT_TO => Rate::MAX_VALUE,
            ShippingTableRateInterface::QTY_TO => Rate::MAX_VALUE,
            ShippingTableRateInterface::PRICE_TO => Rate::MAX_VALUE,
            ShippingTableRateInterface::UNIT_WEIGHT_CONVERSION => 1
        ];
    }
}
