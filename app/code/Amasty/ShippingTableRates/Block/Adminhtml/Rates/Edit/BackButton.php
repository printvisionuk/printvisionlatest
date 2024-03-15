<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Block\Adminhtml\Rates\Edit;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Model\MethodIdContainer;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BackButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var MethodIdContainer
     */
    private $methodIdContainer;

    public function __construct(Context $context, MethodIdContainer $methodIdContainer)
    {
        $this->context = $context;
        $this->methodIdContainer = $methodIdContainer;
    }

    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * @return string
     */
    public function getBackUrl(): string
    {
        return $this->context->getUrlBuilder()->getUrl(
            'amstrates/methods/edit/',
            [
                ShippingTableRateInterface::ID => $this->getMethodId(),
                'tab' => 'rates_section'
            ]
        );
    }

    /**
     * @return int|null
     */
    public function getMethodId(): ?int
    {
        return $this->methodIdContainer->getMethodId();
    }
}
