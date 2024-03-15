<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */

namespace Magenest\Barclaycard\Model\ResourceModel\Charge;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(\Magenest\Barclaycard\Model\Charge::class, \Magenest\Barclaycard\Model\Charge::class);
    }
}
