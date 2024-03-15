<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Controller\Adminhtml\Rates;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;
use Amasty\ShippingTableRates\Model\Import\Rate\ImportFlagProvider;
use Amasty\ShippingTableRates\Model\Rate\DataProcessor;
use Amasty\ShippingTableRates\Model\RateFactory;
use Amasty\ShippingTableRates\Model\ResourceModel\Rate;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Rate Save Action
 */
class Save extends Action implements HttpPostActionInterface
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

    /**
     * @var ImportFlagProvider
     */
    private $importFlagProvider;

    /**
     * @var DataProcessor
     */
    private $rateDataProcessor;

    public function __construct(
        Context $context,
        RateFactory $rateFactory,
        Rate $rateResource,
        ImportFlagProvider $importFlagProvider,
        DataProcessor $rateDataProcessor
    ) {
        parent::__construct($context);
        $this->rateFactory = $rateFactory;
        $this->rateResource = $rateResource;
        $this->importFlagProvider = $importFlagProvider;
        $this->rateDataProcessor = $rateDataProcessor;
    }

    public function execute()
    {
        $methodId = null;
        $rate = $this->rateFactory->create();
        $id = (int)$this->getRequest()->getParam(ShippingTableRateInterface::ID);

        if ($id) {
            try {
                $this->rateResource->load($rate, $id);
                $methodId = (int)$rate->getMethodId();
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This Rate does not exist.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');

                return $resultRedirect;
            }
        }

        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->messageManager->addErrorMessage(__('Unable to find a rate to save'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('adminhtml/amtable_method/index');

            return $resultRedirect;
        }

        $isValid = $this->checkData($data);

        if ($isValid) {
            try {
                if (!$methodId) {
                    $methodId = $data['method_id'];
                }

                $data = $this->rateDataProcessor->process($data);
                $rate->addData($data);

                $this->rateResource->setResourceImportFlag($this->importFlagProvider->getImportFlag());
                $this->rateResource->save($rate);
                $this->messageManager->addSuccessMessage(__('Rate has been successfully saved'));

                //fix for save and continue of new rates
                if ($id === null) {
                    $id = $rate->getId();
                }

                $resultRedirect = $this->resultRedirectFactory->create();
                if ($this->getRequest()->getParam('to_method')) {
                    $resultRedirect->setPath('*/methods/edit', ['id' => $methodId]);
                } else {
                    $resultRedirect->setPath('*/rates/newAction', ['method_id' => $methodId]);
                }

                return $resultRedirect;

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('This rate already exist!'));
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/edit', ['id' => $id, 'method_id' => $methodId]);

                return $resultRedirect;
            }
        } else {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/edit', ['id' => $id]);

            return $resultRedirect;
        }
    }

    public function checkData(array $data): bool
    {
        $isValid = true;

        $checkKeys = [
            ['weight_from', 'weight_to'],
            ['qty_from', 'qty_to'],
            ['price_from', 'price_to']
        ];

        $keysLabels = [
            'weight_from' => __('Weight From'),
            'weight_to' => __('Weight To'),
            'qty_from' => __('Qty From'),
            'qty_to' => __('Qty To'),
            'price_from' => __('Price From'),
            'price_to' => __('Price To'),
        ];

        foreach ($checkKeys as $keys) {
            if ($data[$keys[0]] > $data[$keys[1]]) {
                $this->messageManager->addErrorMessage($keysLabels[$keys[0]]
                    . ' ' . __('must be less than') . ' ' . $keysLabels[$keys[1]]);
                $isValid = false;
            }
        }

        return $isValid;
    }
}
