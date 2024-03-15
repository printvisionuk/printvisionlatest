<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */

namespace Magenest\Barclaycard\Controller\Checkout;

use Magenest\Barclaycard\Controller\Checkout;
use Magenest\Barclaycard\Helper\Constant;

class Cancel extends Checkout
{
    public function execute()
    {
        $this->barclayLogger->debug("Checkout: Cancel");
        $params = $this->getRequest()->getParams();
        $this->barclayLogger->debug(var_export($params, true));
        $orderId = $this->getRequest()->getParam('order_id');
        $this->barclayLogger->debug("orderid ". $orderId);
        $order = $this->orderFactory->create()->loadByIncrementId($orderId);
        /** @var \Magenest\Barclaycard\Helper\Encrypter $encrypter */
        $encrypter = $this->_objectManager->create(\Magenest\Barclaycard\Helper\Encrypter::class);
        $params = $this->getRequest()->getParams();
        $shaSign = isset($params['SHASIGN']) ? $params['SHASIGN'] : "";
        $shaOutGen = $encrypter->generateHashShaOut($params);
        $this->barclayLogger->debug("sha gen form local:" . $shaOutGen);
        if (strtolower($shaSign) == strtolower($shaOutGen)) {
            $order->cancel();
            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->save();
            $this->barclayLogger->debug("payment fail, orderId". $orderId);
        } else {
            $this->barclayLogger->debug("validate fail");
        }
        $this->messageManager->addErrorMessage("Payment cancelled");
        $this->_redirect('checkout/cart');
    }
}
