<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\ImportExportBundle\Cache\AttributeCache;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\SkipTransformer;

/**
 * Specialized OrmTransformer for products
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmProductTransformer extends AbstractOrmTransformer
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
    protected $propertyColumns;

    /**
     * Constructor
     *
     * @param RegistryInterface         $doctrine
     * @param PropertyAccessorInterface $propertyAccessor
     * @param GuesserInterface          $guesser
     * @param LabelTransformerInterface $labelTransformer
     * @param ProductManager            $productManager
     * @param AttributeCache            $attributeCache
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        LabelTransformerInterface $labelTransformer,
        ProductManager $productManager,
        AttributeCache $attributeCache
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $labelTransformer);
        $this->productManager = $productManager;
        $this->attributeCache = $attributeCache;
    }

    /**
     * Transforms an array in a product
     *
     * @param array $data
     *
     * @throws InvalidItemException
     * @return ProductInterface
     */
    public function transform(array $data)
    {
        $this->initializeAttributes($data);

        return $this->doTransform($this->productManager->getFlexibleName(), $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntity($class, array $data)
    {
        $identifierAttribute = $this->attributeCache->getIdentifierAttribute();
        $product = $this->productManager->getImportProduct(
            $this->attributeCache->getAttributes(),
            $identifierAttribute,
            $data[$identifierAttribute->getCode()]
        );

        if (!$product) {
            $product = $this->productManager->createProduct();
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    protected function setProperties($class, $entity, array $data)
    {
        $flexibleValueClass = $this->productManager->getFlexibleValueName();

        foreach ($this->propertyColumns as $label) {
            $columnInfo = $this->labelTransformer->transform($class, $label);
            $transformerInfo = $this->getTransformerInfo($class, $columnInfo);
            $error = $this->setProperty($entity, $columnInfo, $transformerInfo, $data[$label]);
            if ($error) {
                $this->errors[$label] = array($error);
            }
            unset($data[$label]);
        }

        $requiredAttributeCodes = $this->attributeCache->getRequiredAttributeCodes($entity);
        foreach ($data as $label => $value) {
            $columnInfo = $this->labelTransformer->transform($class, $label);
            $attribute = $this->attributeCache->getAttribute($columnInfo['name']);
            $columnInfo['propertyPath'] = $attribute->getBackendType();
            $transformerInfo = $this->getTransformerInfo($flexibleValueClass, $columnInfo);
            if ('' != trim($value) || in_array($columnInfo['name'], $requiredAttributeCodes)) {
                $error = $this->setProductValue($entity, $attribute, $columnInfo, $transformerInfo, $value);
                if ($error) {
                    $this->errors[$label] = array($error);
                }
            }
        }
    }

    /**
     * Sets a product value
     *
     * @param ProductInterface $product
     * @param ProductAttribute $attribute
     * @param ColumnInfo       $columnInfo
     * @param array            $transformerInfo
     * @param mixed            $value
     *
     * @return array
     */
    protected function setProductValue(
        ProductInterface $product,
        ProductAttribute $attribute,
        ColumnInfo $columnInfo,
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
    protected function getProductValue(ProductInterface $product, ColumnInfo $columnInfo)
    {
        $productValue = $product->getValue($columnInfo['name'], $columnInfo['locale'], $columnInfo['scope']);
        if (!$productValue) {
            $productValue = $product->createValue($columnInfo['name'], $columnInfo['locale'], $columnInfo['scope']);
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
        if ($this->attributeCache->isInitialized()) {
            return;
        }

        $class = $this->productManager->getFlexibleName();
        $columnsInfo = $this->labelTransformer->transform($class, array_keys($data));
        $metadata = $this->doctrine->getManager()->getClassMetadata($class);
        $attributeColumnInfos = array();
        $this->propertyColumns = array();
        foreach ($columnsInfo as $columnInfo) {
            $propertyPath = $columnInfo['propertyPath'];
            if ($metadata->hasField($propertyPath) || $metadata->hasAssociation($propertyPath)) {
                $this->propertyColumns[] = $columnInfo['label'];
            } else {
                $attributeColumnInfos[] = $columnInfo;
            }
        }
        $this->attributeCache->initialize($attributeColumnInfos);
    }
}
