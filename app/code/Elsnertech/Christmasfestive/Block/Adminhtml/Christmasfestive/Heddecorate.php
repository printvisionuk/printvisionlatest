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

class Heddecorate extends BaseBlock
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
     * Function Construct
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
     * Function check header Enable or not
     *
     * @return boolean
     */
    public function headerEnable()
    {
        $headermoduleStatus = $this->christmasHelper->getConfig('christmas/generalheader/headerenable');
        return $headermoduleStatus;
    }

    /**
     * Function check header fromdate
     *
     * @return int
     */
    public function headerFromdate()
    {
        $headerfromdate = $this->christmasHelper->getConfig('christmas/generalheader/hedfromdate');
        $headerstartdate = strtotime($headerfromdate);
        return $headerstartdate;
    }

    /**
     * Function check header todate
     *
     * @return int
     */
    public function headerTodate()
    {
        $headertodate = $this->christmasHelper->getConfig('christmas/generalheader/hedtodate');
        $headerenddate = strtotime($headertodate);
        return $headerenddate;
    }

    /**
     * Function check headerPattern to filepath
     *
     * @return string
     */
    public function headerPattern()
    {
        $header_imgupload = $this->christmasHelper->getConfig('christmas/generalheader/header_file_upload');
        $storeManager = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $header_path=  $storeManager . 'Christmasfestive/' . $header_imgupload;
        return $header_path;
    }

    /**
     * Function check module newYearball Enable or not
     *
     * @return boolean
     */
    public function newYearballEnable()
    {
        $ballmoduleStatus = $this->christmasHelper->getConfig('christmas/newyearball/ballenable');
        return $ballmoduleStatus;
    }

    /**
     * Function check ball fromdate
     *
     * @return int
     */
    public function ballFromdate()
    {
        $ballfromdate = $this->christmasHelper->getConfig('christmas/newyearball/ballfromdate');
        $ballstartdate = strtotime($ballfromdate);

        return $ballstartdate;
    }
    
    /**
     * Function check ball todate
     *
     * @return int
     */
    public function ballTodate()
    {
        $balltodate = $this->christmasHelper->getConfig('christmas/newyearball/balltodate');
        $ballenddate = strtotime($balltodate);

        return $ballenddate;
    }

    /**
     * Function check ballPattern to filepath
     *
     * @return string
     */
    public function ballPattern()
    {
        $ball_imgupload = $this->christmasHelper->getConfig('christmas/newyearball/ball_file_upload');
        $storeManager = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $ball_path = $storeManager . 'Christmasfestive/' . $ball_imgupload;
        return $ball_path;
    }

    /**
     * Function check ball Position
     *
     * @return int
     */
    public function ballPosition()
    {
        $ballpostion = $this->christmasHelper->getConfig('christmas/newyearball/ballpostion');
        return $ballpostion;
    }

    /**
     * Function check santaEnable Enable or not
     *
     * @return boolean
     */
    public function santaEnable()
    {
        $santamoduleStatus = $this->christmasHelper->getConfig('christmas/generalsantacluse/santaenable');
        return $santamoduleStatus;
    }

    /**
     * Function check santa fromdate
     *
     * @return int
     */
    public function santaFromdate()
    {
        $santafromdate = $this->christmasHelper->getConfig('christmas/generalsantacluse/santafromdate');
        $santastartdate = strtotime($santafromdate);
        return $santastartdate;
    }

    /**
     * Function check santa todate
     *
     * @return int
     */
    public function santaTodate()
    {
        $santatodate = $this->christmasHelper->getConfig('christmas/generalsantacluse/santatodate');
        $santaenddate = strtotime($santatodate);
        return $santaenddate;
    }

    /**
     * Function check santaPattern to filepath
     *
     * @return int
     */
    public function santaPattern()
    {
        $santa_imgupload = $this->christmasHelper->getConfig('christmas/generalsantacluse/santacustom_file_upload');
        $storeManager = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $santa_path = $storeManager . 'Christmasfestive/' . $santa_imgupload;
        return $santa_path;
    }
}
