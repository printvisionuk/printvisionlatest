<?php
/**
 * Copyright © 2020 Design'N'Buy (inquiry@designnbuy.com). All rights reserved.
 * 
 */
namespace Designnbuy\CustomOptionPlugin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

    /**
     * List Of DesignTool Type
     *
     * @return array
     */
    public function getDesigntoolTypeOptions()
    {
        return [
            ['value'=>'none', 'label'=> __('None')],
            ['value'=>'sizes', 'label'=> __('Sizes')],  
			['value'=>'width', 'label'=> __('Width')],
			['value'=>'height', 'label'=> __('Height')],
			['value'=>'material', 'label'=> __('Material')],
			['value'=>'measurement_unit', 'label'=> __('Measurement Unit')],
        ];
    }

}
