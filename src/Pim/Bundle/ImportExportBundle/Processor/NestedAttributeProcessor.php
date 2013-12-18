<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface;
use Pim\Bundle\ImportExportBundle\Transformer\ORMTransformer;
use Pim\Bundle\ImportExportBundle\Validator\Import\ImportValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Processor for nested attribute imports
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NestedAttributeProcessor extends TransformerProcessor
{
    /**
     * @var string
     */
    protected $optionClass;

    /**
     * Constructor
     *
     * @param ImportValidatorInterface $validator
     * @param TranslatorInterface      $translator
     * @param ORMTransformer           $transformer
     * @param string                   $class
     * @param string                   $optionClass
     */
    public function __construct(
        ImportValidatorInterface $validator,
        TranslatorInterface $translator,
        ORMTransformer $transformer,
        $class,
        $optionClass
    ) {
        parent::__construct($validator, $translator, $transformer, $class, true);
        $this->optionClass = $optionClass;
    }

    /**
     * {@inheritdoc}
     */
    protected function transform($item)
    {
        $optionsData = array();
        if (isset($item['options'])) {
            $optionsData = $item['options'];
            unset($item['options']);
        }

        $attribute = parent::transform($item);
        $this->setOptions($attribute, $optionsData);
    }

    /**
     * Sets the options of the attribute
     * 
     * @param ProductAttributeInterface $attribute
     * @param array $optionsData
     */
    protected function setOptions(ProductAttributeInterface $attribute, array $optionsData)
    {
        foreach ($optionsData as $optionData) {
            $optionData['attribute'] = $attribute->getCode();
            $option = $this->transformer->transform($this->optionClass, $optionData);
            $attribute->addOption($option);
        }
    }
}
