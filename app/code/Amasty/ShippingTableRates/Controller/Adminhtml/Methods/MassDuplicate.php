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
use Amasty\ShippingTableRates\Model\Method\DuplicateProcessor;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDuplicate extends Action implements HttpPostActionInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DuplicateProcessor
     */
    private $duplicateProcessor;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DuplicateProcessor $duplicateProcessor,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        $this->duplicateProcessor = $duplicateProcessor;
    }

    /**
     * Mass update status action
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $updatedCount = 0;
        /** @var Method $method */
        foreach ($collection->getItems() as $method) {
            try {
                $this->duplicateProcessor->execute($method);
                $updatedCount++;
            } catch (\Exception $exception) {
                $this->logger->error($exception->getLogMessage());
            }
        }

        if ($updatedCount) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been duplicated.', $updatedCount)
            );
        } else {
            $this->messageManager->addErrorMessage(
                __('No record(s) have been duplicated.', $updatedCount)
            );
        }

        return $this->resultFactory
            ->create(ResultFactory::TYPE_REDIRECT)
            ->setPath('*/*/index');
    }
}
