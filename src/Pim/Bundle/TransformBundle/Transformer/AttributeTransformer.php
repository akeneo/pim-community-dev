<?php

namespace Pim\Bundle\TransformBundle\Transformer;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\TransformBundle\Cache\DoctrineCache;
use Doctrine\Common\Persistence\ManagerRegistry;

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
     * @var AttributeManager
     */
    protected $attributeManager;

    /**
     * @var DoctrineCache
     */
    protected $doctrineCache;

    /**
     * Constructor
     *
     * @param RegistryInterface              $doctrine
     * @param PropertyAccessorInterface      $propertyAccessor
     * @param GuesserInterface               $guesser
     * @param ColumnInfoTransformerInterface $colInfoTransformer
     * @param EntityTransformerInterface     $transformerRegistry
     * @param AttributeManager               $attributeManager
     * @param DoctrineCache                  $doctrineCache
     */
    public function __construct(
        ManagerRegistry $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $colInfoTransformer,
        EntityTransformerInterface $transformerRegistry,
        AttributeManager $attributeManager,
        DoctrineCache $doctrineCache
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $colInfoTransformer, $transformerRegistry);
        $this->attributeManager = $attributeManager;
        $this->doctrineCache = $doctrineCache;
    }

    /**
     * {@inheritdoc}
     */
    protected function setProperties($class, $entity, array $data)
    {
        if (isset($data['options'])) {
            $optionsData = $data['options'];
            unset($data['options']);
        }

        parent::setProperties($class, $entity, $data);

        if (isset($optionsData)) {
            $this->setOptions($class, $entity, $optionsData);
        }
    }

    /**
     * Sets the options of the attribute
     *
     * @param string            $class
     * @param AbstractAttribute $attribute
     * @param array             $optionsData
     */
    protected function setOptions($class, AbstractAttribute $attribute, array $optionsData)
    {
        $this->doctrineCache->setReference($attribute);
        $optionClass = $this->attributeManager->getAttributeOptionClass();
        foreach ($optionsData as $code => $optionData) {
            $optionData['attribute'] = $attribute->getCode();
            if (!isset($optionData['code'])) {
                $optionData['code'] = $code;
            }
            $option = $this->transformNestedEntity($class, 'options', $optionClass, $optionData);
            $attribute->addOption($option);
            $this->doctrineCache->setReference($option);
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
