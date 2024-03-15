<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Controller\Adminhtml\Rates;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Model\MethodIdContainer;
use Amasty\ShippingTableRates\Model\RateFactory;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_ShippingTableRates::amstrates';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * @var Rate
     */
    private $rateResource;

    /**
     * @var MethodIdContainer
     */
    private $methodIdContainer;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RateFactory $rateFactory,
        Rate $rateResource,
        MethodIdContainer $methodIdContainer
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->rateFactory = $rateFactory;
        $this->rateResource = $rateResource;
        $this->methodIdContainer = $methodIdContainer;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam(ShippingTableRateInterface::ID);
        $methodId = (int)$this->getRequest()->getParam(ShippingTableRateInterface::METHOD_ID);

        if ($id) {
            try {
                $rate = $this->rateFactory->create();
                $this->rateResource->load($rate, $id);
                $methodId = (int)$rate->getMethodId();
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This Rate does not exist.'));

                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');

                return $resultRedirect;
            }
        }

        if ($methodId) {
            $this->methodIdContainer->setMethodId($methodId);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_ShippingTableRates::amstrates')
            ->addBreadcrumb(__('Table Rates'), __('Table Rates'));
        $resultPage->getConfig()->getTitle()->prepend(__('Rate Configuration'));

        return $resultPage;
    }
}
