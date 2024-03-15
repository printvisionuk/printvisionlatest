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
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class Index extends BaseBlock
{

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ChristmasHelper as ChristmasHelper
     */
    protected $christmasHelper;

    /**
     * Function construct
     *
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param ChristmasContext $context
     * @param ChristmasHelper $christmasHelper
     * @param array $data
     */
    public function __construct(
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        ChristmasContext $context,
        ChristmasHelper $christmasHelper,
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->christmasHelper = $christmasHelper;
        parent::__construct($context);
    }

    /**
     * Function Check Module Enable or Not
     *
     * @return boolean
     */
    public function moduleEnable()
    {
        $moduleEnableStatus = $this->christmasHelper->getConfig('christmas/generalenable/modulenable');
        return $moduleEnableStatus;
    }

    /**
     * Function Check current Date time
     *
     * @return int
     */
    public function currentDate()
    {
        $date = $this->dateTime->gmtDate();
        $current_date = strtotime(date('Y-m-d', strtotime($date)));
        return $current_date;
    }

    /**
     * Function check footer Enable or not
     *
     * @return boolean
     */
    public function footerEnable()
    {
        $footermoduleStatus = $this->christmasHelper->getConfig('christmas/generalfooter/enable');
        return $footermoduleStatus;
    }

    /**
     * Function check footer fromdate
     *
     * @return int
     */
    public function footerFromdate()
    {
        $footerfromdate = $this->christmasHelper->getConfig('christmas/generalfooter/start_date');

        if (!empty($footerfromdate)) {
            $footerstartdate = strtotime($footerfromdate);
            return $footerstartdate;
        }

        return null;
    }

    /**
     * Function check footer todate
     *
     * @return int
     */
    public function footerTodate()
    {
        $footertodate = $this->christmasHelper->getConfig('christmas/generalfooter/todate');

        if (!empty($footertodate)) {
            $footerenddate = strtotime($footertodate);
            return $footerenddate;
        }

        return null;
    }

    /**
     * Function check footerPattern to filepath
     *
     * @return string
     */
    public function footerPattern()
    {
        $footer_imgupload = $this->christmasHelper->getConfig('christmas/generalfooter/custom_file_upload');
        $storeManager = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $footer_path = $storeManager . 'Christmasfestive/' . $footer_imgupload;
        return $footer_path;
    }

    /**
     * Function check snowman Enable or not
     *
     * @return boolean
     */
    public function snowmanEnable()
    {
        $snowmanmoduleStatus = $this->christmasHelper->getConfig('christmas/generalsnowman/snowmanenable');
        return $snowmanmoduleStatus;
    }

    /**
     * Function check snowman fromdate
     *
     * @return int
     */
    public function snowmanFromdate()
    {
        $snowmanfromdate = $this->christmasHelper->getConfig('christmas/generalsnowman/snowmanfromdate');
      
        if (!empty($snowmanfromdate)) {
            $snowmanstartdate = strtotime($snowmanfromdate);
            return $snowmanstartdate;
        }

        return null;
    }

    /**
     * Function check snowman todate
     *
     * @return int
     */
    public function snowmanTodate()
    {
        $snowmantodate = $this->christmasHelper->getConfig('christmas/generalsnowman/snowmantodate');
        if (!empty($snowmantodate)) {
            $snowmanenddate = strtotime($snowmantodate);
            return $snowmanenddate;
        }
    
        return null;
    }

    /**
     * Function check snowmanPattern to filepath
     *
     * @return string
     */
    public function snowmanPattern()
    {
        $snowman_imgupload = $this->christmasHelper->getConfig('christmas/generalsnowman/snowman_file_upload');
        $storeManager = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $snowman_path = $storeManager . 'Christmasfestive/' . $snowman_imgupload;
        return $snowman_path;
    }

    /**
     * Function check snowman Position
     *
     * @return int
     */
    public function snowmanPosition()
    {
        $snowmanpostion = $this->christmasHelper->getConfig('christmas/generalsnowman/snowmanpostion');
        return $snowmanpostion;
    }
}
