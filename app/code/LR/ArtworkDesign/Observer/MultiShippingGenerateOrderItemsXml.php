<?php
namespace LR\ArtworkDesign\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class MultiShippingGenerateOrderItemsXml implements ObserverInterface
{
    /**
     *
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \LR\ArtworkDesign\Helper\Data
     */
    protected $helperData;

    /**
     * @var \LR\ArtworkDesign\Helper\ArtWorkData
     */
    protected $artWorkData;

    /**
     * __construct function
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param \LR\ArtworkDesign\Helper\Data $helperData
     * @param \LR\ArtworkDesign\Helper\ArtWorkData $artWorkData
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        \LR\ArtworkDesign\Helper\Data $helperData,
        \LR\ArtworkDesign\Helper\ArtWorkData $artWorkData
    ) {
        $this->orderRepository = $orderRepository;
        $this->helperData = $helperData;
        $this->artWorkData = $artWorkData;
    }

    /**
     * Execute function
     *
     * @param Observer $observer
     * @return mixed
     */
    public function execute(Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();

        foreach ($orderIds as $orderId) {
            // Load the order by its ID
            $order = $this->orderRepository->get($orderId);
            $this->helperData->generateOrderItemXmlFile($order);
            $this->artWorkData->getArtWorkData($order);
            $this->artWorkData->sendArtworkMissingMail($order);
        }
    }
}
