<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Pim\Bundle\CatalogBundle\Manager\AttributeManagerInterface;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\ImportExportBundle\Cache\EntityCache;

/**
 * Attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTransformer extends NestedEntityTransformer
{
    /**
     * @var AttributeManagerInterface
     */
    protected $attributeManager;

    /**
     * @var EntityCache
     */
    protected $entityCache;

    /**
     * Constructor
     *
     * @param RegistryInterface              $doctrine
     * @param PropertyAccessorInterface      $propertyAccessor
     * @param GuesserInterface               $guesser
     * @param ColumnInfoTransformerInterface $columnInfoTransformer
     * @param EntityTransformerInterface     $transformerRegistry
     * @param AttributeManagerInterface      $attributeManager
     * @param EntityCache                    $entityCache
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $columnInfoTransformer,
        EntityTransformerInterface $transformerRegistry,
        AttributeManagerInterface $attributeManager,
        EntityCache $entityCache
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $columnInfoTransformer, $transformerRegistry);
        $this->attributeManager = $attributeManager;
        $this->entityCache = $entityCache;
    }

    /**
     * {@inheritdoc}
     */
    protected function setProperties($class, $entity, array $data)
    {
        if (isset($data['options'])) {
            $optionsData = $data['options'];
            unset($data['options']);
            parent::setProperties($class, $entity, $data);
            $this->setOptions($class, $entity, $optionsData);
        } else {
            parent::setProperties($class, $entity, $data);
        }
    }

    /**
     * Sets the options of the attribute
     *
     * @param string             $class
     * @param AttributeInterface $attribute
     * @param array              $optionsData
     */
    protected function setOptions($class, AttributeInterface $attribute, array $optionsData)
    {
        $this->entityCache->setReference($attribute);
        $optionClass = $this->attributeManager->getAttributeOptionClass();
        foreach ($optionsData as $code => $optionData) {
            $optionData['attribute'] = $attribute->getCode();
            if (!isset($optionData['code'])) {
                $optionData['code'] = $code;
            }
            $option = $this->transformNestedEntity($class, 'options', $optionClass, $optionData);
            $attribute->addOption($option);
            $this->entityCache->setReference($option);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($class, array $data)
    {
        return $this->attributeManager->createAttribute($data['type']);
    }
}
