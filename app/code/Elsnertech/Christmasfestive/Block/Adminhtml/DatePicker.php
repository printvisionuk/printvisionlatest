<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2023 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Christmasfestive
 */

namespace Elsnertech\Christmasfestive\Block\Adminhtml;

use Magento\Framework\Registry;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class DatePicker extends Field
{

    /**
     * @var  Registry
     */
    protected $_coreRegistry;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
    
    /**
     * Function _getElementHtml
     *
     * @param AbstractElement $element
     * @return void
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $baseURL = $this->getBaseUrl();
        $html = $element->getElementHtml();
        $calpath = $baseURL . 'pub/media/systemcalendar/';
        if (!$this->_coreRegistry->registry('datepicker_loaded')) {
            $html .= '<style type="text/css">input.datepicker {
                background-image: url(' . $calpath . 'calenar.svg) !important;
                background-position: calc(100% - 8px) center;
                background-repeat: no-repeat;
            }
            input.datepicker.disabled,input.datepicker[disabled] {
                pointer-events: none;
                }
            </style>';
            $this->_coreRegistry->registry('datepicker_loaded', 1);
        }
        $html .= '<script type="text/javascript">
            require(["jquery", "jquery/ui"], function () {
                jQuery(document).ready(function () {
                    jQuery("#' . $element->getHtmlId() . '").datepicker( { dateFormat: "yy-m-d" } );
                        
                    var el = document.getElementById("' . $element->getHtmlId() . '");
                    el.className = el.className + " datepicker";
                });
            });
            </script>';
        return $html;
    }
}
