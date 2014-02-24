<?php

namespace Pim\Bundle\TransformBundle\Transformer;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\TransformBundle\Cache\AttributeCache;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\SkipTransformer;
use Pim\Bundle\BaseConnectorBundle\Reader\CachedReader;

/**
 * Specialized ORMTransformer for products
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTransformer extends EntityTransformer
{
    /**
     * @staticvar the identifier attribute type
     */
    const IDENTIFIER_ATTRIBUTE_TYPE = 'pim_catalog_identifier';

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var AttributeCache
     */
    protected $attributeCache;

    /**
     * @var CachedReader
     */
    protected $associationReader;

    /**
     * @var \Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute
     */
    protected $identifierAttribute;

    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * @var array
     */
    protected $propertyColumnsInfo = array();

    /**
     * @var array
     */
    protected $attributeColumnsInfo = array();

    /**
     * @var array
     */
    protected $associationColumnsInfo = array();

    /**
     * @var array
     */
    protected $readLabels = array();

    /**
     * Constructor
     *
     * @param RegistryInterface              $doctrine
     * @param PropertyAccessorInterface      $propertyAccessor
     * @param GuesserInterface               $guesser
     * @param ColumnInfoTransformerInterface $columnInfoTransformer
     * @param ProductManager                 $productManager
     * @param AttributeCache                 $attributeCache
     * @param CachedReader                   $associationReader
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $columnInfoTransformer,
        ProductManager $productManager,
        AttributeCache $attributeCache,
        CachedReader $associationReader
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $columnInfoTransformer);
        $this->productManager = $productManager;
        $this->attributeCache = $attributeCache;
        $this->associationReader = $associationReader;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($class, array $data, array $defaults = array())
    {
        $this->initializeAttributes($data);

        return parent::transform($class, $data, $defaults);
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($class, array $data)
    {
        return $this->productManager->getFlexibleRepository()->findByReference(
            $data[$this->identifierAttribute->getCode()]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($class, array $data)
    {
        return $this->productManager->createProduct();
    }

    /**
     * {@inheritdoc}
     */
    protected function setProperties($class, $entity, array $data)
    {
        $this->setProductProperties($class, $entity, $data);
        $this->setProductValues($class, $entity, $data);
        $this->setAssociations($entity, $data);
    }

    /**
     * Sets the product entitie's properties
     *
     * @param string $class
     * @param type   $entity
     * @param array  $data
     */
    protected function setProductProperties($class, $entity, array $data)
    {
        foreach ($this->propertyColumnsInfo as $columnInfo) {
            $label = $columnInfo->getLabel();
            if (!array_key_exists($label, $data)) {
                continue;
            }
            $transformerInfo = $this->getTransformerInfo($class, $columnInfo);
            $error = $this->setProperty($entity, $columnInfo, $transformerInfo, $data[$label]);
            if ($error) {
                $this->errors[$class][$label] = array($error);
            }
        }
    }

    /**
     * Sets the product entitie's properties
     *
     * @param string $class
     * @param type   $entity
     * @param array  $data
     */
    protected function setProductValues($class, $entity, array $data)
    {
        $requiredAttributeCodes = $this->attributeCache->getRequiredAttributeCodes($entity);
        $flexibleValueClass = $this->productManager->getFlexibleValueName();
        $this->transformedColumns[$flexibleValueClass] = array();
        foreach ($this->attributeColumnsInfo as $columnInfo) {
            $label = $columnInfo->getLabel();
            if (!array_key_exists($label, $data)) {
                continue;
            }
            $transformerInfo = $this->getTransformerInfo($flexibleValueClass, $columnInfo);
            $value = $data[$label];
            if ((is_scalar($value) && '' !== trim($value)) ||
                in_array($columnInfo->getName(), $requiredAttributeCodes)
            ) {
                $error = $this->setProductValue($entity, $columnInfo, $transformerInfo, $value);
                if ($error) {
                    $this->errors[$class][$label] = array($error);
                }
            }
        }
    }

    /**
     * Sets the product's associations
     *
     * @param type  $entity
     * @param array $data
     */
    protected function setAssociations($entity, array $data)
    {
        if (!count($this->associationColumnsInfo)) {
            return;
        }

        $associations = array();
        foreach ($this->associationColumnsInfo as $columnInfo) {
            $label = $columnInfo->getLabel();
            if (!array_key_exists($label, $data)) {
                continue;
            }
            $key = $entity->getReference() . '.' . $columnInfo->getName();
            $suffixes = $columnInfo->getSuffixes();
            $lastSuffix = array_pop($suffixes);
            if (!isset($associations[$key])) {
                $associations[$key] = array(
                    'owner'           => $entity->getReference(),
                    'associationType' => $columnInfo->getName(),
                );
            }
            $associations[$key][$lastSuffix] =  $data[$label] ?: array();
        }

        foreach ($associations as $association) {
            $this->associationReader->addItem($association);
        }
    }

    /**
     * Sets a product value
     *
     * @param ProductInterface    $product
     * @param ColumnInfoInterface $columnInfo
     * @param array               $transformerInfo
     * @param mixed               $value
     *
     * @return array
     */
    protected function setProductValue(
        ProductInterface $product,
        ColumnInfoInterface $columnInfo,
        array $transformerInfo,
        $value
    ) {
        if ($transformerInfo[0] instanceof SkipTransformer) {
            return array();
        }
        $productValue = $this->getProductValue($product, $columnInfo);

        return parent::setProperty($productValue, $columnInfo, $transformerInfo, $value);
    }

    /**
     * Returns a ProductValue
     *
     * @param ProductInterface $product
     * @param array            $columnInfo
     *
     * @return ProductValueInterface
     */
    protected function getProductValue(ProductInterface $product, ColumnInfoInterface $columnInfo)
    {
        $productValue = $product->getValue($columnInfo->getName(), $columnInfo->getLocale(), $columnInfo->getScope());
        if (null === $productValue) {
            $productValue = $this->productManager->createProductValue();
            $productValue->setAttribute($columnInfo->getAttribute());
            $productValue->setLocale($columnInfo->getLocale());
            $productValue->setScope($columnInfo->getScope());
            $product->addValue($productValue);
        }

        return $productValue;
    }

    /**
     * Initializes the attribute cache
     *
     * @param array $data
     */
    protected function initializeAttributes($data)
    {
        $labels = array_diff(array_keys($data), $this->readLabels);
        if (!count($labels)) {
            return;
        }

        $class = $this->productManager->getFlexibleName();
        $columnsInfo = $this->columnInfoTransformer->transform($class, $labels);

        $this->attributes += $this->attributeCache->getAttributes($columnsInfo);
        foreach ($columnsInfo as $columnInfo) {
            $this->readLabels[] = $columnInfo->getLabel();
            $columnName = $columnInfo->getName();
            $suffixes = $columnInfo->getSuffixes();
            $lastSuffix = array_pop($suffixes);
            if (in_array($lastSuffix, array('groups', 'products'))) {
                $this->associationColumnsInfo[] = $columnInfo;
            } elseif (isset($this->attributes[$columnName])) {
                $attribute = $this->attributes[$columnName];
                $columnInfo->setAttribute($attribute);
                $this->attributeColumnsInfo[] = $columnInfo;
                if (static::IDENTIFIER_ATTRIBUTE_TYPE == $attribute->getAttributeType()) {
                    $this->identifierAttribute = $attribute;
                }
            } else {
                $columnInfo->setAttribute(null);
                $this->propertyColumnsInfo[] = $columnInfo;
            }
        }
        $this->initialized = true;
    }

    /**
     * Clears the cache
     */
    public function reset()
    {
        $this->identifierAttribute = null;
        $this->attributes = array();
        $this->attributeColumnsInfo = array();
        $this->propertyColumnsInfo = array();
        $this->associationColumnsInfo = array();
        $this->readLabels = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformedColumnsInfo($class)
    {
        return array_merge(
            parent::getTransformedColumnsInfo($class),
            $this->transformedColumns[$this->productManager->getFlexibleValueName()]
        );
    }
}
