<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2023 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Christmasfestive
 */
namespace Elsnertech\Christmasfestive\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class SnowFlakes implements ArrayInterface
{

    /**
     * Function toOptionArray
     *
     * @return void
     */
    public function toOptionArray()
    {
        return [
            ['value' => '&#x2744;', 'label' => __('❄')],
            ['value' => '&#10053;', 'label' => __('❅')],
            ['value' => '&#10053;', 'label' => __('❆')],
            ['value' => '&#9733;', 'label' => __('★')],
            ['value' => '&#9734;', 'label' => __('☆')]
        ];
    }
}
