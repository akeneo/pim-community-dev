<?php

namespace Pim\Bundle\TransformBundle\Transformer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\TransformBundle\Cache\DoctrineCache;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Pim\Component\Catalog\Factory\AttributeFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6
 */
class AttributeTransformer extends NestedEntityTransformer
{
    /** @var AttributeFactory */
    protected $attributeFactory;

    /** @var DoctrineCache */
    protected $doctrineCache;

    /** @var string */
    protected $optionClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry                $doctrine
     * @param PropertyAccessorInterface      $propertyAccessor
     * @param GuesserInterface               $guesser
     * @param ColumnInfoTransformerInterface $colInfoTransformer
     * @param EntityTransformerInterface     $transformerRegistry
     * @param AttributeFactory               $attributeFactory
     * @param DoctrineCache                  $doctrineCache
     * @param string                         $optionClass
     */
    public function __construct(
        ManagerRegistry $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $colInfoTransformer,
        EntityTransformerInterface $transformerRegistry,
        AttributeFactory $attributeFactory,
        DoctrineCache $doctrineCache,
        $optionClass
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $colInfoTransformer, $transformerRegistry);

        $this->attributeFactory = $attributeFactory;
        $this->doctrineCache    = $doctrineCache;
        $this->optionClass      = $optionClass;
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
     * @param string             $class
     * @param AttributeInterface $attribute
     * @param array              $optionsData
     */
    protected function setOptions($class, AttributeInterface $attribute, array $optionsData)
    {
        $this->doctrineCache->setReference($attribute);
        foreach ($optionsData as $code => $optionData) {
            $optionData['attribute'] = $attribute->getCode();
            if (!isset($optionData['code'])) {
                $optionData['code'] = $code;
            }
            $option = $this->transformNestedEntity($class, 'options', $this->optionClass, $optionData);
            $attribute->addOption($option);
            $this->doctrineCache->setReference($option);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($class, array $data)
    {
        return $this->attributeFactory->createAttribute($data['type']);
    }
}
