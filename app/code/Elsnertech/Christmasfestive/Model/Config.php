<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2023 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Christmasfestive
 */
namespace Elsnertech\Christmasfestive\Model;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\DB\Transaction;
use Magento\Framework\App\Config\ValueFactory;

class Config extends DataObject
{

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ValueInterface
     */
    protected $_backendModel;

    /**
     * @var Transaction
     */
    protected $_transaction;

    /**
     * @var ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var int $_storeId
     */
    protected $_storeId;

    /**
     * @var string $_storeCode
     */
    protected $_storeCode;

    /**
     * Function construct
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ValueInterface $backendModel
     * @param Transaction $transaction
     * @param ValueFactory $configValueFactory
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ValueInterface $backendModel,
        Transaction $transaction,
        ValueFactory $configValueFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_backendModel = $backendModel;
        $this->_transaction = $transaction;
        $this->_configValueFactory = $configValueFactory;
        $this->_storeId=(int)$this->_storeManager->getStore()->getId();
        $this->_storeCode=$this->_storeManager->getStore()->getCode();
    }

    /**
     * Function check get CurrentStore ConfigValue
     *
     * @param string $path
     * @return void
     */
    public function getCurrentStoreConfigValue($path)
    {
        return $this->_scopeConfig->getValue($path, 'store', $this->_storeCode);
    }
    
    /**
     * Function check set CurrentStore ConfigValue
     *
     * @param string $path
     * @param int $value
     * @return void
     */
    public function setCurrentStoreConfigValue($path, $value)
    {
        $data = [
                    'path' => $path,
                    'scope' =>  'stores',
                    'scope_id' => $this->_storeId,
                    'scope_code' => $this->_storeCode,
                    'value' => $value,
                ];

        $this->_backendModel->addData($data);
        $this->_transaction->addObject($this->_backendModel);
        $this->_transaction->save();
    }
}
