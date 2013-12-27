<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\ImportExportBundle\Cache\AttributeCache;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\SkipTransformer;

/**
 * Specialized ORMTransformer for products
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMProductTransformer extends ORMTransformer
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
     * @var array
     */
    protected $attributes;

    /**
     * @var ProductAttributeInterface
     */
    protected $identifierAttribute;

    /**
     * @var boolean
     */
    protected $initialized=false;

    /**
     * @var array
     */
    protected $propertyColumnsInfo;

    /**
     * @var array
     */
    protected $attributeColumnsInfo;

    /**
     * Constructor
     *
     * @param RegistryInterface              $doctrine
     * @param PropertyAccessorInterface      $propertyAccessor
     * @param GuesserInterface               $guesser
     * @param ColumnInfoTransformerInterface $columnInfoTransformer
     * @param ProductManager                 $productManager
     * @param AttributeCache                 $attributeCache
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $columnInfoTransformer,
        ProductManager $productManager,
        AttributeCache $attributeCache
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $columnInfoTransformer);
        $this->productManager = $productManager;
        $this->attributeCache = $attributeCache;
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
        return $this->productManager->getImportProduct(
            $this->attributes,
            $this->identifierAttribute,
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
        $flexibleValueClass = $this->productManager->getFlexibleValueName();

        foreach ($this->propertyColumnsInfo as $columnInfo) {
            $label = $columnInfo->getLabel();
            $transformerInfo = $this->getTransformerInfo($class, $columnInfo);
            $error = $this->setProperty($entity, $columnInfo, $transformerInfo, $data[$label]);
            if ($error) {
                $this->errors[$label] = array($error);
            }
        }

        $requiredAttributeCodes = $this->attributeCache->getRequiredAttributeCodes($entity);
        foreach ($this->attributeColumnsInfo as $columnInfo) {
            $label = $columnInfo->getLabel();
            $transformerInfo = $this->getTransformerInfo($flexibleValueClass, $columnInfo);
            $value = $data[$label];
            if ('' !== trim($value) || in_array($columnInfo->getName(), $requiredAttributeCodes)) {
                $error = $this->setProductValue($entity, $columnInfo, $transformerInfo, $value);
                if ($error) {
                    $this->errors[$label] = array($error);
                }
            }
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
        if (!$productValue) {
            $productValue = $product
                ->createValue($columnInfo->getName(), $columnInfo->getLocale(), $columnInfo->getScope());
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
        if ($this->initialized) {
            return;
        }
        $class = $this->productManager->getFlexibleName();
        $columnsInfo = $this->columnInfoTransformer->transform($class, array_keys($data));
        $this->attributes = $this->attributeCache->getAttributes($columnsInfo);
        $this->attributeColumnsInfo = array();
        $this->propertyColumnsInfo = array();
        foreach ($columnsInfo as $columnInfo) {
            $columnName = $columnInfo->getName();
            if (isset($this->attributes[$columnName])) {
                $attribute = $this->attributes[$columnName];
                $columnInfo->setAttribute($attribute);
                $this->attributeColumnsInfo[] = $columnInfo;
                if (static::IDENTIFIER_ATTRIBUTE_TYPE == $attribute->getAttributeType()) {
                    $this->identifierAttribute = $attribute;
                }
            } else {
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
        $this->attributes = null;
        $this->identifierAttribute = null;
        $this->attributeColumnsInfo = null;
        $this->propertyColumnsInfo = null;
        $this->initialized = false;
    }
}
