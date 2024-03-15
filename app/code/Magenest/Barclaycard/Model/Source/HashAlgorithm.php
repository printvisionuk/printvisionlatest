<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */
namespace Magenest\Barclaycard\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class HashAlgorithm implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'sha1',
                'label' => __('SHA-1'),
            ],
            [
                'value' => 'sha256',
                'label' => __('SHA-256'),
            ],
            [
                'value' => 'sha512',
                'label' => __('SHA-512'),
            ],
        ];
    }
}
