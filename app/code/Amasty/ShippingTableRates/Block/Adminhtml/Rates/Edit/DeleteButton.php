<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Block\Adminhtml\Rates\Edit;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getButtonData(): array
    {
        $data = [];
        if ($this->getEntityId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __('Are you sure?')
                    . '\', \'' . $this->getDeleteUrl() . '\', {"data": {}})',
                'sort_order' => 20,
            ];
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl(): string
    {
        return $this->context->getUrlBuilder()
            ->getUrl('*/*/delete', [ShippingTableRateInterface::ID => $this->getEntityId()]);
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return (int)$this->context->getRequest()->getParam(ShippingTableRateInterface::ID);
    }
}
