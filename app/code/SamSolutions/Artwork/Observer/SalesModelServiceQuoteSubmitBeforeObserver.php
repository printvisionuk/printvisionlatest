<?php

namespace SamSolutions\Artwork\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

class SalesModelServiceQuoteSubmitBeforeObserver implements ObserverInterface
{
    
    private $serializer;

    public function __construct(
        SerializerInterface $serializer
    )
    {
        $this->serializer = $serializer;
    }

    /**
     * Add order information into GA block to render on checkout success pages
     *
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $quote = $observer->getQuote();
        $order = $observer->getOrder();


        foreach ($quote->getAllVisibleItems() as $quoteItem) {
                $quoteItems[$quoteItem->getId()] = $quoteItem;
            }

        foreach ($order->getAllVisibleItems() as $orderItem) {

            $quoteItemId = $orderItem->getQuoteItemId();


            $additionalOptions = [];
            
            if ($additionalOptions = $quoteItem->getOptionByCode('additional_options')) {
                if (isset($additionalOptions['value'])) {
                    $options = $orderItem->getProductOptions();
                    $options['additional_options'] = $this->serializer->unserialize($additionalOptions->getValue());
                    $orderItem->setProductOptions($options);
                }

            }

        }
    }

}
