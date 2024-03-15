<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\DynamicOptionsBase\Model\Product\Option;

use Magento\Catalog\Model\Product\Option;
use MageWorx\DynamicOptionsBase\Api\Data\DynamicOptionsConfigReaderInterface;
use MageWorx\DynamicOptionsBase\Api\DynamicOptionRepositoryInterface;
use MageWorx\DynamicOptionsBase\Api\Data\DynamicOptionInterface;
use MageWorx\DynamicOptionsBase\Model\Source\MeasurementUnits;

class AdditionalHtml
{
    /**
     * @var DynamicOptionsConfigReaderInterface
     */
    protected $config;

    /**
     * @var DynamicOptionRepositoryInterface
     */
    protected $repository;

    /**
     * @var Option
     */
    protected $option;

    /**
     * @var DynamicOptionInterface
     */
    protected $dynamicOption;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * @var int
     */
    protected $optionNumber = 0;

    /**
     * AdditionalHtml constructor.
     *
     * @param DynamicOptionsConfigReaderInterface $config
     * @param DynamicOptionRepositoryInterface $repository
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     */
    public function __construct(
        DynamicOptionsConfigReaderInterface $config,
        DynamicOptionRepositoryInterface $repository,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->config     = $config;
        $this->repository = $repository;
        $this->assetRepo = $assetRepo;
    }

    /**
     * @param \DOMDocument $dom
     * @param Option $option
     * @return void
     */
    public function getAdditionalHtml($dom, $option)
    {
        if ($this->out($dom, $option)) {
            return;
        }

        $this->dom    = $dom;
        $this->option = $option;

        try {
            $dynamicOption = $this->repository->getById((int)$option->getOptionId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return;
        }

        if ($dynamicOption->getOptionId() != $option->getOptionId()) {
            return;
        }

        $this->optionNumber++;

        $this->dynamicOption = $dynamicOption;

        $this->addMeasurementUnit();

        $this->addValidations('validate-number');
        $this->addValidations('validate-zero-or-greater');
        $this->addHtmlClass('mageworx-dynamic-option');
        $hint = '';

        if ($this->dynamicOption->getMinValue()) {
            $this->addValidations('mageworx-dynamic-option-min-value-rule');
            $hint .= $this->wrapMessage(__('Min Value: %1', $this->dynamicOption->getMinValue()));
        }

        if ($this->dynamicOption->getMaxValue()) {
            $this->addValidations('mageworx-dynamic-option-max-value-rule');
            $hint .= $this->wrapMessage(__('Max Value: %1', $this->dynamicOption->getMaxValue()));
        }

        if ($this->dynamicOption->getStep() > 0) {
            $this->addValidations('mageworx-dynamic-option-step-rule');
            $hint .= $this->wrapMessage(__('Step: %1', $this->dynamicOption->getStep()));
        }

        if ($hint) {
            $this->addHint($hint);
        }
    }

    /**
     * Add Measurement Unit
     */
    private function addMeasurementUnit()
    {
        $html            = '<span class="dynamic_option_measurement_unit">(';
        $measurementUnit = $this->dynamicOption->getMeasurementUnit();
        if ($this->isImageAvailable($measurementUnit)) {
            //$html .= $this->getOptionImage();
        }
        $html .= __('in');
        $html .= ' ' . $measurementUnit;
        $html .= ')</span>';

        $this->insertBeforeOptionField($html);
    }

    /**
     * @param string $measurementUnit
     * @return bool
     */
    private function isImageAvailable(string $measurementUnit)
    {
        $result = false;
        switch ($measurementUnit) {
            case MeasurementUnits::METER:
            case MeasurementUnits::CENTIMETER:
            case MeasurementUnits::MILLIMETER:
            case MeasurementUnits::FOOT:
            case MeasurementUnits::INCH:
                $result = true;
                break;
            default:
                $result = false;
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getOptionImage()
    {
        if ($this->optionNumber > 3) {
            return '';
        }
        $url = $this->assetRepo->getUrl('MageWorx_DynamicOptionsBase::images/arrow_'. $this->optionNumber.'.svg');

        return '<img src="' . $url . '" alt="Image" class="dynamic_option_arrow"> ';
    }
    /**
     * Add html class for field
     *
     * @param string $class
     */
    protected function addHtmlClass(string $class)
    {
        $xpath = new \DOMXPath($this->dom);

        $optionDivs = $xpath->query("//*[@name='options[" . $this->option->getOptionId() . "]']");
        foreach ($optionDivs as $optionDiv) {
            $optionCssClass = $optionDiv->getAttribute('class') ?: '';
            $optionDiv->setAttribute('class', $optionCssClass . ' ' . $class);
        }
    }

    /**
     * Add html class for field
     *
     * @param string $rule
     */
    protected function addValidations(string $rule)
    {
        $xpath = new \DOMXPath($this->dom);

        $optionDivs = $xpath->query("//*[@name='options[" . $this->option->getOptionId() . "]']");
        foreach ($optionDivs as $optionDiv) {
            $validate = $optionDiv->getAttribute('data-validate') ?: '{}';
            $comma    = $optionDiv->getAttribute('data-validate') ? ',' : '';
            $validate = rtrim($validate, '}"');
            $optionDiv->setAttribute('data-validate', $validate . $comma . '"' . $rule . '":true}');
        }
    }

    /**
     * Add message under option
     *
     * @param \Magento\Framework\Phrase $message
     */
    protected function wrapMessage(\Magento\Framework\Phrase $message)
    {
        $html = '<div class="mageworx-dynamic-option-hint-item">';
        $html .= $message;
        $html .= '</div>';

        return $html;
    }

    /**
     * @param string $hint
     */
    protected function addHint(string $hint)
    {
        $imageUrl = $this->assetRepo->getUrl('MageWorx_DynamicOptionsBase::images/hint.svg');
        $image = '<img src="' . $imageUrl .
            '" alt="Hint" class="dynamic_option_hint_icon" id="mageworx_dynamic_option_hint_icon_' .
            $this->option->getOptionId() . '"> ';

        $html =  $image . '<div class="dynamic_option_hint" id="mageworx_dynamic_option_hint_'
            .  $this->option->getOptionId() . '">' . $hint . '</div>';

        $this->insertBeforeOptionField($html);
    }

    /**
     * @param string $html
     */
    protected function insertBeforeOptionField(string $html)
    {
        $xpath      = new \DOMXPath($this->dom);
        $targetNode = $xpath->query("//label[contains(@class,'label')]");

        if (!$targetNode->length) {
            return;
        }

        $tpl = new \DOMDocument();
        $tpl->loadHtml($html);

        $targetNode = $targetNode->item(0);
        $newNode    = $this->dom->importNode($tpl->documentElement, true);
        $targetNode->insertBefore($newNode);
        $targetNode->insertBefore($this->dom->createTextNode("\n"));
    }

    /**
     * @param \DOMDocument $dom
     * @param Option $option
     * @return bool
     */
    protected function out($dom, $option)
    {
        if (!$dom || !$option) {
            return true;
        }

        if (!$this->config->isEnabled()) {
            return true;
        }

        return false;
    }
}
