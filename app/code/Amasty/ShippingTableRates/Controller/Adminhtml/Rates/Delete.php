<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Controller\Adminhtml\Rates;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Model\RateFactory;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Delete Rate of Method Action
 */
class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_ShippingTableRates::amstrates';

    /**
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * @var Rate
     */
    private $rateResource;

    public function __construct(
        Action\Context $context,
        RateFactory $rateFactory,
        Rate $rateResource
    ) {
        parent::__construct($context);
        $this->rateFactory = $rateFactory;
        $this->rateResource = $rateResource;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam(ShippingTableRateInterface::ID);
        if (!$id) {
            $this->messageManager->addErrorMessage(__('Unable to find a rate to delete'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/methods/index');

            return $resultRedirect;
        }

        try {
            /** @var \Amasty\ShippingTableRates\Model\Rate $rate */
            $rate = $this->rateFactory->create();
            $this->rateResource->load($rate, $id);
            $methodId = $rate->getMethodId();
            $this->rateResource->delete($rate);

            $this->messageManager->addSuccessMessage(__('Rate has been deleted'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/methods/edit', ['id' => $methodId, 'tab' => 'rates_section']);

            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/methods/index');

            return $resultRedirect;
        }
    }
}
