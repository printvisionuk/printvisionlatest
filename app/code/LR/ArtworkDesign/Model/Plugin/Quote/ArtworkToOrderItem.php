<?php
namespace LR\ArtworkDesign\Model\Plugin\Quote;

use Magento\Framework\Serialize\SerializerInterface;

class ArtworkToOrderItem
{
 
       private $serializer;

       public function __construct(
        SerializerInterface $serializer
       )
       {
        $this->serializer = $serializer;
       }

       public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
       ) {
              $orderItem = $proceed($item, $additional);
              // $orderItem->setCustomField($item->getCustomField());

              if ($additionalOptions = $item->getOptionByCode('additional_options')) {
                     if (isset($additionalOptions['value'])) {
                           $options = $orderItem->getProductOptions();
                           $options['additional_options'] = $this->serializer->unserialize($additionalOptions->getValue());
                           $orderItem->setProductOptions($options);
                     }

              }

              return $orderItem;
       }
}