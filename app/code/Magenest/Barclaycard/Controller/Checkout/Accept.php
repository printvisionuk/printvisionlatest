<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */

namespace Magenest\Barclaycard\Controller\Checkout;

use Magenest\Barclaycard\Controller\Checkout;
use Magenest\Barclaycard\Helper\Constant;
use Magento\Framework\Exception\LocalizedException;

class Accept extends Checkout
{
    public function execute()
    {
        $this->barclayLogger->debug("Checkout: Accept");
        try {
            /**
             * @var \Magento\Sales\Model\Order $order
             */
            $params = $this->getRequest()->getParams();
            $this->barclayLogger->debug(var_export($params, true));
            $ncerror = $this->getRequest()->getParam('NCERROR');
            usleep(3000000); //sleep 3 sec waiting for webhook processing.
            $orderId = $this->getRequest()->getParam('order_id');
            $order = $this->orderFactory->create()->loadByIncrementId($orderId);
            $payment = $order->getPayment();
            if(!$payment ||
                ($payment && ($payment->getAdditionalInformation('barclay_payment_success') == true))
            ){
                return $this->_redirect('checkout/onepage/success');
            }
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            if ($ncerror == "0") {
                $this->processOrder();
                return $this->_redirect('checkout/onepage/success');
            }else{
                $this->messageManager->addErrorMessage("Payment error");
                return $this->_redirect('checkout/cart');
            }

        }
        catch (LocalizedException $e) {
            $this->barclayLogger->debug($e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('checkout/cart');
        }
        catch (\Exception $e) {
            $this->barclayLogger->debug($e->getMessage());
            $this->messageManager->addErrorMessage("Payment error");
            return $this->_redirect('checkout/cart');
        }
    }
}
