<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */

namespace Magenest\Barclaycard\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class PaymentAction implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'authorize',
                'label' => __('Authorize Only (Authorisation)'),
            ],
            [
                'value' => 'authorize_capture',
                'label' => __('Authorize and Capture (Sale)')
            ]
        ];
    }
}
