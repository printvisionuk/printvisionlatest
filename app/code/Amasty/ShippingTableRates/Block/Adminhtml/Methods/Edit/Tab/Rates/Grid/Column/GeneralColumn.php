<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Block\Adminhtml\Methods\Edit\Tab\Rates\Grid\Column;

class GeneralColumn extends \Magento\Backend\Block\Widget\Grid\Column
{
    /**
     * Add decorated column value to column
     *
     * @return array
     */
    public function getFrameCallback()
    {
        return [$this, 'decorateColumn'];
    }

    /**
     * Decorate column values because in \Magento\Backend\Block\Widget\Grid\Export::367 value can not be null
     *
     * @param string $value
     * @param \Amasty\ShippingTableRates\Model\Rate $row
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool $isExport
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function decorateColumn($value, $row, $column, $isExport)
    {
        if ($value === null) {
            $value = "";
        }

        return $value;
    }
}
