<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Translation\TranslatorInterface;
use Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface;
use Pim\Bundle\ImportExportBundle\Transformer\ORMAttributeTransformer;
use Pim\Bundle\ImportExportBundle\Transformer\ORMTransformer;
use Pim\Bundle\ImportExportBundle\Validator\Import\ImportValidatorInterface;
use Pim\Bundle\ImportExportBundle\Cache\EntityCache;

/**
 * Processor for nested attribute imports
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NestedAttributeProcessor extends AbstractTransformerProcessor
{
    
    /**
     * @var ORMAttributeTransformer
     */
    protected $attributeTransformer;

    /**
     * @var ORMTransformer
     */
    protected $optionTransformer;

    /**
     * @var \Pim\Bundle\ImportExportBundle\Cache\EntityCache
     */
    protected $entityCache;

    /**
     * @var string
     */
    protected $optionClass;

    /**
     * Constructor
     * 
     * @param ImportValidatorInterface $validator
     * @param TranslatorInterface $translator
     * @param ORMAttributeTransformer $attributeTransformer
     * @param ORMTransformer $optionTransformer
     * @param string $optionClass
     */
    public function __construct(
        ImportValidatorInterface $validator, 
        TranslatorInterface $translator,
        ORMAttributeTransformer $attributeTransformer,
        ORMTransformer $optionTransformer,
        EntityCache $entityCache,
        $optionClass
    ) {
        parent::__construct($validator, $translator);
        
        $this->attributeTransformer = $attributeTransformer;
        $this->optionTransformer = $optionTransformer;
        $this->optionClass = $optionClass;
        $this->entityCache = $entityCache;
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

        $attribute = $this->attributeTransformer->transform($item);
        $this->setOptions($attribute, $optionsData);
        
        return $attribute;
    }

    /**
     * Sets the options of the attribute
     * 
     * @param ProductAttributeInterface $attribute
     * @param array $optionsData
     */
    protected function setOptions(ProductAttributeInterface $attribute, array $optionsData)
    {
        foreach ($optionsData as $code => $optionData) {
            if (!isset($optionData['code'])) {
                $optionData['code'] = $code;
            }
            $option = $this->optionTransformer->transform($this->optionClass, $optionData);
            $attribute->addOption($option);
            $this->entityCache->setReference($option);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getTransformedColumnsInfo()
    {
        return array_merge(
            $this->optionTransformer->getTransformedColumnsInfo(),
            $this->attributeTransformer->getTransformedColumnsInfo()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTransformerErrors()
    {
        return $this->optionTransformer->getErrors() + $this->attributeTransformer->getErrors();
    }
}
