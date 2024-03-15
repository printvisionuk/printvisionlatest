<?php
namespace LR\ArtworkDesign\Observer;

use Magento\Framework\Event\ObserverInterface;

class GenerateOrderXml implements ObserverInterface
{
    /**
     * @var \LR\ArtworkDesign\Helper\ArtWorkData
     */
    protected $artWorkData;

    /**
     * __construct function
     *
     * @param \LR\ArtworkDesign\Helper\ArtWorkData $artWorkData
     */
    public function __construct(
        \LR\ArtworkDesign\Helper\ArtWorkData $artWorkData
    ) {
        $this->artWorkData = $artWorkData;
    }

    /**
     * Generate order xml file
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order != null || !empty($order)) {
            $this->artWorkData->getArtWorkData($order);
        }
    }
}
