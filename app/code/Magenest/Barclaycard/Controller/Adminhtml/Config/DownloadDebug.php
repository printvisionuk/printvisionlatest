<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */

namespace Magenest\Barclaycard\Controller\Adminhtml\Config;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;

class DownloadDebug extends \Magento\Backend\App\Action
{
    protected $directory_list;
    protected $fileFactory;
    protected $driver;

    public function __construct(
        Action\Context $context,
        DirectoryList $directory_list,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem\DriverInterface $driver
    ) {
        $this->directory_list = $directory_list;
        $this->fileFactory = $fileFactory;
        $this->driver = $driver;
        parent::__construct($context);
    }

    public function execute()
    {
        $version = $this->getRequest()->getParam('version');
        $filename = "barclaycard_debugfile_".$version."_".date("Ymd").".log";
        $file = $this->directory_list->getPath("var")."/log/barclaycard/debug.log";
        return $this->fileFactory->create($filename, $this->driver->fileGetContents($file), "tmp");
    }
}
