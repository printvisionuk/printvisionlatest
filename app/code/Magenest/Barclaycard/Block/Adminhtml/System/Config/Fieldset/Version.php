<?php
/**
 * Created by Magenest JSC.
 * Date: 22/12/2020
 * Time: 9:41
 */
namespace Magenest\Barclaycard\Block\Adminhtml\System\Config\Fieldset;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Backend\Block\Template;
use Magento\Framework\Module\Dir\Reader as DirReader;

class Version extends Template implements RendererInterface
{
    protected $dirReader;

    public function __construct(
        \Magento\Framework\Filesystem\DriverInterface $driver,
        \Magento\Framework\Serialize\SerializerInterface $jsonSerializer,
        DirReader $dirReader,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->driver = $driver;
        $this->_jsonSerializer = $jsonSerializer;
        $this->dirReader = $dirReader;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return mixed
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '';
        if ($element->getData('group')['id'] == 'version') {
            $html = $this->toHtml();
        }
        return $html;
    }

    public function getVersion()
    {
        $installVersion = "unidentified";
        $composer = $this->getComposerInformation("Magenest_Barclaycard");

        if ($composer) {
            $installVersion = $composer['version'];
        }

        return $installVersion;
    }

    public function getComposerInformation($moduleName)
    {
        $dir = $this->dirReader->getModuleDir("", $moduleName);

        if ($this->driver->isExists($dir.'/composer.json')) {
            return $this->_jsonSerializer->unserialize($this->driver->fileGetContents($dir.'/composer.json'));
        }

        return false;
    }

    public function getTemplate()
    {
        return 'Magenest_Barclaycard::system/config/fieldset/version.phtml';
    }

    public function getDownloadDebugUrl()
    {
        return $this->getUrl('barclaycard/config/downloadDebug', ['version'=>$this->getVersion()]);
    }
}
