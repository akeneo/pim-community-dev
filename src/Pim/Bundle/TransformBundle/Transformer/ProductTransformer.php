<?php

namespace Pim\Bundle\TransformBundle\Transformer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\BaseConnectorBundle\Reader\CachedReader;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\TransformBundle\Cache\AttributeCache;
use Pim\Bundle\TransformBundle\Exception\MissingIdentifierException;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\SkipTransformer;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Specialized transformer for products
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTransformer extends EntityTransformer
{
    /** @var AttributeCache */
    protected $attributeCache;

    /** @var CachedReader */
    protected $associationReader;

    /** @var AttributeInterface */
    protected $identifierAttribute;

    /** @var array */
    protected $attributes = array();

    /** @var array */
    protected $propertyColumnsInfo = array();

    /** @var array */
    protected $attributeColumnsInfo = array();

    /** @var array */
    protected $assocColumnsInfo = array();

    /** @var array */
    protected $readLabels = array();

    /** @var ProductTemplateUpdaterInterface */
    protected $templateUpdater;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $productValueClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry                 $doctrine
     * @param PropertyAccessorInterface       $propertyAccessor
     * @param GuesserInterface                $guesser
     * @param ColumnInfoTransformerInterface  $colInfoTransformer
     * @param AttributeCache                  $attributeCache
     * @param CachedReader                    $associationReader
     * @param ProductTemplateUpdaterInterface $templateUpdater
     * @param ProductBuilderInterface         $productBuilder
     * @param ProductRepositoryInterface      $productRepository
     * @param string                          $productClass
     * @param string                          $productValueClass
     */
    public function __construct(
        ManagerRegistry $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $colInfoTransformer,
        AttributeCache $attributeCache,
        CachedReader $associationReader,
        ProductTemplateUpdaterInterface $templateUpdater,
        ProductBuilderInterface $productBuilder,
        ProductRepositoryInterface $productRepository,
        $productClass,
        $productValueClass
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $colInfoTransformer);
        $this->productRepository = $productRepository;
        $this->attributeCache = $attributeCache;
        $this->associationReader = $associationReader;
        $this->templateUpdater = $templateUpdater;
        $this->productBuilder = $productBuilder;
        $this->productClass = $productClass;
        $this->productValueClass = $productValueClass;
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
        if (!$this->identifierAttribute) {
            throw new MissingIdentifierException();
        }

        return $this->productRepository->findOneByIdentifier($data[$this->identifierAttribute->getCode()]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($class, array $data)
    {
        return $this->productBuilder->createProduct();
    }

    /**
     * {@inheritdoc}
     */
    protected function setProperties($class, $entity, array $data)
    {
        $this->setProductProperties($class, $entity, $data);
        $this->setProductValues($class, $entity, $data);
        $this->setProductValuesFromVariantGroup($entity);
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
        $reqAttributeCodes = $this->attributeCache->getRequiredAttributeCodes($entity);
        $flexibleValueClass = $this->productValueClass;
        $this->transformedColumns[$flexibleValueClass] = array();
        foreach ($this->attributeColumnsInfo as $columnInfo) {
            $label = $columnInfo->getLabel();
            if (!array_key_exists($label, $data)) {
                continue;
            }
            $transformerInfo = $this->getTransformerInfo($flexibleValueClass, $columnInfo);
            $value = $data[$label];
            if ((is_scalar($value) && '' !== trim($value)) ||
                in_array($columnInfo->getName(), $reqAttributeCodes)
            ) {
                $error = $this->setProductValue($entity, $columnInfo, $transformerInfo, $value);
                if ($error) {
                    $this->errors[$class][$label] = array($error);
                }
            }
        }
    }

    /**
     * @param ProductInterface $product
     */
    protected function setProductValuesFromVariantGroup(ProductInterface $product)
    {
        $variantGroup = $product->getVariantGroup();
        if ($variantGroup !== null && $variantGroup->getProductTemplate() !== null) {
            $template = $variantGroup->getProductTemplate();
            $this->templateUpdater->update($template, [$product]);
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
        if (!count($this->assocColumnsInfo)) {
            return;
        }

        $associations = array();
        foreach ($this->assocColumnsInfo as $columnInfo) {
            $label = $columnInfo->getLabel();
            if (!array_key_exists($label, $data) || empty($data[$label])) {
                continue;
            }
            $key = $entity->getReference() . '.' . $columnInfo->getName();
            $suffixes = $columnInfo->getSuffixes();
            $lastSuffix = array_pop($suffixes);
            if (!isset($associations[$key])) {
                $associations[$key] = array(
                    'association_type' => $columnInfo->getName(),
                    'owner'            => $entity->getReference(),
                );
            }
            $associations[$key][$lastSuffix] =  $data[$label];
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
     * @param ProductInterface    $product
     * @param ColumnInfoInterface $columnInfo
     *
     * @return ProductValueInterface
     */
    protected function getProductValue(ProductInterface $product, ColumnInfoInterface $columnInfo)
    {
        $productValue = $product->getValue($columnInfo->getName(), $columnInfo->getLocale(), $columnInfo->getScope());
        if (null === $productValue) {
            $attribute = $columnInfo->getAttribute();
            $locale = $columnInfo->getLocale();
            $scope = $columnInfo->getScope();
            $productValue = $this->productBuilder->createProductValue($attribute, $locale, $scope);
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

        $class = $this->productClass;
        $columnsInfo = $this->colInfoTransformer->transform($class, $labels);

        $this->attributes += $this->attributeCache->getAttributes($columnsInfo);
        foreach ($columnsInfo as $columnInfo) {
            $this->readLabels[] = $columnInfo->getLabel();
            $columnName = $columnInfo->getName();
            $suffixes = $columnInfo->getSuffixes();
            $lastSuffix = array_pop($suffixes);
            if (in_array($lastSuffix, array('groups', 'products'))) {
                $this->assocColumnsInfo[] = $columnInfo;
            } elseif (isset($this->attributes[$columnName])) {
                $attribute = $this->attributes[$columnName];
                $columnInfo->setAttribute($attribute);
                $this->attributeColumnsInfo[] = $columnInfo;
                if (AttributeTypes::IDENTIFIER == $attribute->getAttributeType()) {
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
        $this->assocColumnsInfo = array();
        $this->readLabels = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformedColumnsInfo($class)
    {
        return array_merge(
            parent::getTransformedColumnsInfo($class),
            $this->transformedColumns[$this->productValueClass]
        );
    }
}
