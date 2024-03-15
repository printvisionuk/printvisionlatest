<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */
namespace Magenest\Barclaycard\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Cancelx implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $orderCollection = \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        /**
         * @var \Magento\Sales\Model\Order $order
         * @var \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection
         */
        $orderCollection->getLastItem()->setData('status', 'canceled');
        foreach ($orderCollection as $item) {
            $item->getStatus();
        }
        $orderCollection->save();
    }
}
