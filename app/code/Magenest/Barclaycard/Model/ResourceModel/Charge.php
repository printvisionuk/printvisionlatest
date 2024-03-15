<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */
namespace Magenest\Barclaycard\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Charge extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('magenest_barclaycard_pending_charge', 'id');
    }
}
