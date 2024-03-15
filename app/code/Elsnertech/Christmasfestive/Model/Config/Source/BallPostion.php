<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2023 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Christmasfestive
 */
namespace Elsnertech\Christmasfestive\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class BallPostion implements ArrayInterface
{

    /**
     * Function toOptionArray
     *
     * @return void
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'left', 'label' => __('Top-Left')],
            ['value' => 'right', 'label' => __('Top-Right')]
        ];
    }
}
