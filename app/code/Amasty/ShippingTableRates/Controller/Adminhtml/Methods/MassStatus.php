<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package Shipping Table Rates for Magento 2
 */

namespace Amasty\ShippingTableRates\Controller\Adminhtml\Methods;

use Amasty\ShippingTableRates\Model\ResourceModel\Method\CollectionFactory;
use Amasty\ShippingTableRates\Model\Method;
use Amasty\ShippingTableRates\Model\Method\MethodSave;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassStatus extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_ShippingTableRates::amstrates';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var MethodSave
     */
    private $methodSave;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        MethodSave $methodSave,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->methodSave = $methodSave;
        $this->logger = $logger;
    }

    /**
     * Mass update status action
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $status = $this->getRequest()->getParam('status');
        $updatedCount = 0;

        if ($status !== null) {
            /** @var Method $method */
            foreach ($collection->getItems() as $method) {
                try {
                    $method->setIsActive((bool)$status);
                    $this->methodSave->execute($method);
                    $updatedCount++;
                } catch (\Exception $exception) {
                    $this->logger->error($exception->getLogMessage());
                }
            }
        }

        if ($updatedCount) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been updated.', $updatedCount)
            );
        } else {
            $this->messageManager->addErrorMessage(
                __('No record(s) have been updated.', $updatedCount)
            );
        }

        return $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT)
            ->setPath('*/*/index');
    }
}
