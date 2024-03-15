<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Block\Adminhtml\System\Config\Form;

use Amasty\ShippingTableRates\Model\Quote\Inventory\MsiModuleStatusInspector;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class MsiAlgorithm extends Field
{
    /**
     * @var MsiModuleStatusInspector
     */
    private $msiModuleStatusInspector;

    public function __construct(
        Context $context,
        MsiModuleStatusInspector $msiModuleStatusInspector,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->msiModuleStatusInspector = $msiModuleStatusInspector;
    }

    /**
     * @param AbstractElement $element
     * @param string $html
     * @return string
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _decorateRowHtml(AbstractElement $element, $html): string
    {
        if (!$this->msiModuleStatusInspector->isEnabled()) {
            return '';
        }

        return parent::_decorateRowHtml($element, $html);
    }
}
