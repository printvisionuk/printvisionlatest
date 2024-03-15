<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */


namespace Magenest\Barclaycard\Controller\Checkout;

use Magenest\Barclaycard\Controller\Checkout;
use Magenest\Barclaycard\Helper\Constant;

class Response extends Checkout
{
    public function execute()
    {
        try {
            /**
             * @var \Magento\Sales\Model\Order $order
             */
            $params = $this->getRequest()->getParams();
            $this->barclayLogger->debug("Checkout: Response OK");
            $this->barclayLogger->debug(var_export($params, true));
            $ncerror = $this->getRequest()->getParam('NCERROR');
            $orderId = $this->getRequest()->getParam('order_id');
            $order = $this->orderFactory->create()->loadByIncrementId($orderId);
            $payment = $order->getPayment();
            if($payment->getAdditionalInformation('barclay_payment_success') == true){
                return;
            }
            if ($ncerror == "0") {
                $this->processOrder();
            }
        } catch (\Exception $e) {
            $this->barclayLogger->debug($e->getMessage());
        }
    }
}
