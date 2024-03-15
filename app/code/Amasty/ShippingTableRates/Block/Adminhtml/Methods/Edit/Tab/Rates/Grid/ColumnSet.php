<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Block\Adminhtml\Methods\Edit\Tab\Rates\Grid;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Block\Adminhtml\Methods\Edit\Tab\Rates\Grid\Column\GeneralColumn;
use Amasty\ShippingTableRates\Helper\Data;
use Amasty\ShippingTableRates\Model\Source\Option\WeightRoundingOptions;
use Magento\Backend\Model\Widget\Grid\Row\UrlGeneratorFactory;
use Magento\Backend\Model\Widget\Grid\SubTotals;
use Magento\Backend\Model\Widget\Grid\Totals;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;

/**
 * Columns for Shipping Rate Grid
 */
class ColumnSet extends \Magento\Backend\Block\Widget\Grid\ColumnSet
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var WeightRoundingOptions
     */
    private $weightRoundingOptions;

    public function __construct(
        Context $context,
        UrlGeneratorFactory $generatorFactory,
        SubTotals $subtotals,
        Totals $totals,
        Data $helper,
        WeightRoundingOptions $weightRoundingOptions,
        array $data
    ) {
        $this->helper = $helper;
        $this->weightRoundingOptions = $weightRoundingOptions;
        parent::__construct($context, $generatorFactory, $subtotals, $totals, $data);
    }

    protected function _prepareLayout()
    {
        $this->addColumn(ShippingTableRateInterface::COUNTRY, [
            'header' => __('Country'),
            'header_export' => ShippingTableRateInterface::COUNTRY,
            'index' => ShippingTableRateInterface::COUNTRY,
            'type' => 'options',
            'options' => $this->helper->getCountries(),
        ]);

        $this->addColumn(ShippingTableRateInterface::STATE, [
            'header' => __('State'),
            'header_export' => ShippingTableRateInterface::STATE,
            'index' => ShippingTableRateInterface::STATE,
            'type' => 'options',
            'options' => $this->helper->getStates(),
        ]);

        $this->addColumn(ShippingTableRateInterface::CITY, [
            'header' => __('City'),
            'header_export' => ShippingTableRateInterface::CITY,
            'index' => ShippingTableRateInterface::CITY,
            'type' => 'text',
        ]);

        $this->addColumn(ShippingTableRateInterface::ZIP_FROM, [
            'header' => __('Zip From'),
            'header_export' => ShippingTableRateInterface::ZIP_FROM,
            'index' => ShippingTableRateInterface::ZIP_FROM,
        ]);

        $this->addColumn(ShippingTableRateInterface::ZIP_TO, [
            'header' => __('Zip To'),
            'header_export' => ShippingTableRateInterface::ZIP_TO,
            'index' => ShippingTableRateInterface::ZIP_TO,
        ]);

        $this->addColumn(ShippingTableRateInterface::PRICE_FROM, [
            'header' => __('Price From'),
            'header_export' => ShippingTableRateInterface::PRICE_FROM,
            'index' => ShippingTableRateInterface::PRICE_FROM,
        ]);

        $this->addColumn(ShippingTableRateInterface::PRICE_TO, [
            'header' => __('Price To'),
            'header_export' => ShippingTableRateInterface::PRICE_TO,
            'index' => ShippingTableRateInterface::PRICE_TO,
        ]);

        $this->addColumn(ShippingTableRateInterface::WEIGHT_FROM, [
            'header' => __('Weight From'),
            'header_export' => ShippingTableRateInterface::WEIGHT_FROM,
            'index' => ShippingTableRateInterface::WEIGHT_FROM,
        ]);

        $this->addColumn(ShippingTableRateInterface::WEIGHT_TO, [
            'header' => __('Weight To'),
            'header_export' => ShippingTableRateInterface::WEIGHT_TO,
            'index' => ShippingTableRateInterface::WEIGHT_TO,
        ]);

        $this->addColumn(ShippingTableRateInterface::QTY_FROM, [
            'header' => __('Qty From'),
            'header_export' => ShippingTableRateInterface::QTY_FROM,
            'index' => ShippingTableRateInterface::QTY_FROM,
        ]);

        $this->addColumn(ShippingTableRateInterface::QTY_TO, [
            'header' => __('Qty To'),
            'header_export' => ShippingTableRateInterface::QTY_TO,
            'index' => ShippingTableRateInterface::QTY_TO,
        ]);

        $this->addColumn(ShippingTableRateInterface::SHIPPING_TYPE, [
            'header' => __('Shipping Type'),
            'header_export' => ShippingTableRateInterface::SHIPPING_TYPE,
            'index' => ShippingTableRateInterface::SHIPPING_TYPE,
            'type' => 'options',
            'options' => $this->helper->getTypes(),
        ]);

        $this->addColumn(ShippingTableRateInterface::COST_BASE, [
            'header' => __('Rate'),
            'header_export' => 'rate',
            'index' => ShippingTableRateInterface::COST_BASE,
        ]);

        $this->addColumn(ShippingTableRateInterface::COST_PERCENT, [
            'header' => __('PPP'),
            'header_export' => 'ppp',
            'index' => ShippingTableRateInterface::COST_PERCENT,
        ]);

        $this->addColumn(ShippingTableRateInterface::COST_PRODUCT, [
            'header' => __('FRPP'),
            'header_export' => 'frpp',
            'index' => ShippingTableRateInterface::COST_PRODUCT,
        ]);

        $this->addColumn(ShippingTableRateInterface::UNIT_WEIGHT_CONVERSION, [
            'header' => __('Weight Unit Conversion Rate'),
            'header_export' => ShippingTableRateInterface::UNIT_WEIGHT_CONVERSION,
            'index' => ShippingTableRateInterface::UNIT_WEIGHT_CONVERSION
        ]);

        $this->addColumn(ShippingTableRateInterface::WEIGHT_ROUNDING, [
            'header' => __('Weight Rounding'),
            'header_export' => ShippingTableRateInterface::WEIGHT_ROUNDING,
            'index' => ShippingTableRateInterface::WEIGHT_ROUNDING,
            'type' => 'options',
            'options' => $this->weightRoundingOptions->toOptionArray()
        ]);

        $this->addColumn(ShippingTableRateInterface::COST_WEIGHT, [
            'header' => __('FRPUW'),
            'header_export' => 'frpuw',
            'index' => ShippingTableRateInterface::COST_WEIGHT,
        ]);

        $this->addColumn(ShippingTableRateInterface::START_WEIGHT, [
            'header' => __('Count weight from'),
            'header_export' => ShippingTableRateInterface::START_WEIGHT,
            'index' => ShippingTableRateInterface::START_WEIGHT,
        ]);

        $this->addColumn(ShippingTableRateInterface::TIME_DELIVERY, [
            'header' => __('Estimated Delivery (days)'),
            'header_export' => 'estimated_delivery',
            'index' => ShippingTableRateInterface::TIME_DELIVERY,
        ]);

        $this->addColumn(ShippingTableRateInterface::NAME_DELIVERY, [
            'header' => __('Name delivery'),
            'header_export' => ShippingTableRateInterface::NAME_DELIVERY,
            'index' => ShippingTableRateInterface::NAME_DELIVERY,
        ]);

        $this->addColumn('sources', [
            'header' => __('Source'),
            'header_export' => 'source',
            'index' => 'source_codes',
        ]);

        $link = $this->getUrl('amstrates/rates/delete') . 'id/$id';
        $this->addColumn('action', [
            'header' => __('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getVid',
            'actions' => [
                [
                    'caption' => __('Delete'),
                    'url' => $link,
                    'field' => 'id',
                    'confirm' => __('Are you sure?')
                ]
            ],
            'filter' => false,
            'sortable' => false,
            'is_system' => true,
        ]);

        return parent::_prepareLayout();
    }

    /**
     * @param string $title
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function addColumn(string $title, array $data, string $class = GeneralColumn::class): void
    {
        $column = $this->getLayout()->createBlock($class, $title)->addData($data);
        $this->setChild($title, $column);
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('amstrates/rates/edit', ['id' => $item->getId()]);
    }
}
