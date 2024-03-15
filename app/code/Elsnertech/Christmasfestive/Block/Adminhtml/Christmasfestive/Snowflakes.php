<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2023 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Christmasfestive
 */
namespace Elsnertech\Christmasfestive\Block\Adminhtml\Christmasfestive;

use Elsnertech\Christmasfestive\Block\Adminhtml\BaseBlock;
use Elsnertech\Christmasfestive\Helper\Data as ChristmasHelper;
use Magento\Framework\View\Element\Template\Context;
use Elsnertech\Christmasfestive\Block\Adminhtml\Context as ChristmasContext;

class Snowflakes extends BaseBlock
{

    /**
     * @var ChristmasHelper as ChristmasHelper
     */
    protected $christmasHelper;

    /**
     * construct function
     *
     * @param ChristmasContext $context
     * @param ChristmasHelper $christmasHelper
     * @param array $data
     */
    public function __construct(
        ChristmasContext $context,
        ChristmasHelper $christmasHelper,
        array $data = []
    ) {
        $this->christmasHelper = $christmasHelper;
        parent::__construct($context);
    }

    /**
     * Function check get JsonOptions
     *
     * @return boolean
     */
    public function getJsonOptions()
    {
        $options = new \Magento\Framework\DataObject();
        $options->flakes = 50;

        $options->color = explode(',', $this->christmasHelper->getConfig(
            'christmas/generalsnowflakes/Snowflakescolors'
        ));

        $options->text = $this->christmasHelper->getConfig('christmas/generalsnowflakes/Snowflakes');

        $options->speed = $this->christmasHelper->getConfig('christmas/generalsnowflakes/snowspedd');

        $options->size = (object) [
            'min' => $this->christmasHelper->getConfig('christmas/generalsnowflakes/minsnowflakes'),

            'max' => $this->christmasHelper->getConfig('christmas/generalsnowflakes/maxsnowflakes')
        ];

        return json_encode($options, true);
    }

    /**
     * Function check module Enable or not
     *
     * @return boolean
     */
    public function moduleEnable()
    {
        $moduleEnableStatus = $this->christmasHelper->getConfig('christmas/generalenable/modulenable');
        return $moduleEnableStatus;
    }
}
