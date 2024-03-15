<?php

namespace SamSolutions\Artwork\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

class CheckoutCartProductAddAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $_request;

    private $_serializer;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutInterface    $layout
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request,
        SerializerInterface   $serializer
    ) {
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_serializer = $serializer;
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
        /* @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getQuoteItem();
        $additionalOptions = [];
        if ($additionalOption = $item->getOptionByCode('additional_options')) {
           // $additionalOptions = (array)json_decode($additionalOption->getValue());
            $additionalOptions = $this->_serializer->unserialize($additionalOption->getValue());
        }

        $isArtwork = $this->_request->getParam('artwork');

        if ($isArtwork == '2') {
            $additionalOptions[] = [
                'label' => 'Artwork After',
                'value' => 'Yes',
            ];

            if (count($additionalOptions) > 0) {
                $item->addOption([
                    'product_id' => $item->getProductId(),//Missing data
                    'code'       => 'additional_options',
                    'value'      => $this->_serializer->serialize($additionalOptions),
                ]);
            }
        }

    }
}
