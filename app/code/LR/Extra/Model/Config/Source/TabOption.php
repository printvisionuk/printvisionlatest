<?php

namespace LR\Extra\Model\Config\Source;

class TabOption implements \Magento\Framework\Data\OptionSourceInterface
{
 public function toOptionArray()
 {
  return [
    ['value' => 'accordion', 'label' => __('Accordion')],
    ['value' => 'tab', 'label' => __('Tab')]
  ];
 }
}
