<?php
namespace Designnbuy\CustomOptionPlugin\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
 
class PricecalculationsAfterAddtoCart implements ObserverInterface
{

    protected $_request;

    public function __construct(
        RequestInterface $request,
        array $data = []
    ) {
        $this->_request = $request;
    }
     
    public function execute(\Magento\Framework\Event\Observer $observer) 
    {
        if($this->_request->getParam('form_data')){
            
            $quote_item = $observer->getEvent()->getQuoteItem();
            $price = base64_decode($this->_request->getParam('form_data'));
            $price = explode("-",$price);

            if(isset($price[1])){
                $price = $price[1] / $quote_item->getQty();
                $quote_item->setCustomPrice($price);
                $quote_item->setOriginalCustomPrice($price);
                $quote_item->getProduct()->setIsSuperMode(true);
            }

        }
        return $this;    
    }
}