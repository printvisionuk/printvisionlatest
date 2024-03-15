<?php
namespace LR\ArtworkDesign\Observer;

use Magento\Framework\Event\ObserverInterface;

class GenerateOrderItemXml implements ObserverInterface
{
    /**
     * @var \LR\ArtworkDesign\Helper\Data
     */
    protected $helperData;

    /**
     * __construct function
     *
     * @param \LR\ArtworkDesign\Helper\Data $helperData
     */
    public function __construct(
        \LR\ArtworkDesign\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Create artwork order file function
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return mixed
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order != null || !empty($order)) {
            $this->helperData->generateOrderItemXmlFile($order);
        }
    }
}
