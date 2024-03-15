<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */
namespace Magenest\Barclaycard\Model;

use Magenest\Barclaycard\Model\ResourceModel\Charge as Resource;
use Magenest\Barclaycard\Model\ResourceModel\Charge\Collection as Collection;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Charge extends AbstractModel
{
    const STATUS_PENDING = "pending";
    protected $_eventPrefix = 'charge_';

    public function __construct(
        Context $context,
        Registry $registry,
        Resource $resource,
        Collection $resourceCollection,
        $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
}
