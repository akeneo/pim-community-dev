<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface;
use Pim\Bundle\ImportExportBundle\Cache\EntityCache;

/**
 * Attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMAttributeTransformer extends ORMTransformer
{
    /**
     * @var ProductManager
     */
    protected $productManager;

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
     * @param ProductManager                 $productManager
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $columnInfoTransformer,
        ProductManager $productManager,
        EntityCache $entityCache
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $columnInfoTransformer);
        $this->productManager = $productManager;
        $this->entityCache = $entityCache;
    }

    /**
     * {@inheritdoc}
     */
    protected function setProperties($class, $entity, array $data)
    {
        $attributeClass = $this->productManager->getAttributeName();
        if ($attributeClass === $class && isset($data['options'])) {
            $optionsData = $data['options'];
            unset($data['options']);
            parent::setProperties($class, $entity, $data);
            $this->setOptions($entity, $optionsData);
        } else {
            parent::setProperties($class, $entity, $data);
        }
    }

    /**
     * Sets the options of the attribute
     *
     * @param ProductAttributeInterface $attribute
     * @param array                     $optionsData
     */
    protected function setOptions(ProductAttributeInterface $attribute, array $optionsData)
    {
        $this->entityCache->setReference($attribute);
        foreach ($optionsData as $code => $optionData) {
            $optionData['attribute'] = $attribute->getCode();
            if (!isset($optionData['code'])) {
                $optionData['code'] = $code;
            }
            $attributeClass = $this->productManager->getAttributeOptionName();
            $option = $this->getEntity($attributeClass, $optionData);
            $this->setProperties($attributeClass, $option, $optionData);
            if (count($this->errors)) {
                break;
            }
            $attribute->addOption($option);
            $this->entityCache->setReference($option);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($class, array $data)
    {
        return ($this->productManager->getAttributeName() === $class)
            ? $this->productManager->createAttribute($data['type'])
            : parent::createEntity($class, $data);
    }
}
