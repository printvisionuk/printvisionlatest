<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Ui\Component\Form\Rate\Field;

use Amasty\ShippingTableRates\Model\Quote\Inventory\MsiModuleStatusInspector;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Field;

class Source extends Field
{
    /**
     * @var MsiModuleStatusInspector
     */
    private $msiModuleStatusInspector;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        MsiModuleStatusInspector $msiModuleStatusInspector,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->msiModuleStatusInspector = $msiModuleStatusInspector;
    }

    public function prepare()
    {
        parent::prepare();
        $this->_data['config']['visible'] = $this->msiModuleStatusInspector->isEnabled();
    }
}
