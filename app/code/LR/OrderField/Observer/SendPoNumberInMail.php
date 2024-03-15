<?php

namespace LR\OrderField\Observer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;

class SendPoNumberInMail implements \Magento\Framework\Event\ObserverInterface
{   
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\Action\Action $controller */
        $transport = $observer->getEvent()->getTransport();
        if ($transport->getOrder() != null) 
        {
            $poNumber = $transport->getOrder()->getPoNumber();
            if ($poNumber) 
            {
                $transport['po_number'] = $poNumber;
            }
        }
    }

}