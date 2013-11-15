<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\ImportExportBundle\Cache\AttributeCache;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\ImportExportBundle\Validator\Import\ProductImportValidator;
use Pim\Bundle\ImportExportBundle\Exception\InvalidValueException;

/**
 * Transforms a CSV product in an entity
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMProductTransformer
{
    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var AttributeCache
     */
    protected $attributeCache;

    /**
     * @var ProductImportValidator
     */
    protected $productValidator;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var array
     */
    protected $propertyTransformers = array();

    /**
     * @var array
     */
    protected $attributeTransformers = array();

    /**
     * Constructor
     *
     * @param ProductManager            $productManager
     * @param ProductImportValidator    $productValidator
     * @param AttributeCache            $attributeCache
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(
        ProductManager $productManager,
        ProductImportValidator $productValidator,
        AttributeCache $attributeCache,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->productManager = $productManager;
        $this->productValidator = $productValidator;
        $this->attributeCache = $attributeCache;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Returns a ProductInterface object from an array of scalar values
     *
     * @param array $values   the values to transform
     * @param array $mapping  a mapping of columns which should be renamed
     * @param array $defaults default values for the object
     *
     * @return ProductInterface
     * @throws InvalidItemException
     */
    public function getProduct(array $values, array $mapping = array(), array $defaults = array())
    {

        $this->mapValues($values, $mapping);
        $attributeValues = array_diff_key($values, $this->propertyTransformers);
        $propertyValues = array_intersect_key($values, $this->propertyTransformers);
        if (!$this->attributeCache->isInitialized()) {
            $this->attributeCache->initialize(array_keys($attributeValues));
        }

        $product = $this->createOrLoadProduct($values);

        $this->setDefaultValues($product, $defaults);
        $errors = array_merge(
            $this->setPropertyValues($product, $propertyValues),
            $this->setAttributeValues($product, $attributeValues)
        );
        if (count($errors)) {
            throw new InvalidItemException(implode("\n", $errors), $values);
        }

        return $product;
    }

    /**
     * Adds a property transformer
     *
     * @param string                       $propertyPath
     * @param PropertyTransformerInterface $transformer
     * @param array                        $options
     */
    public function addPropertyTransformer(
        $propertyPath,
        Property\PropertyTransformerInterface $transformer,
        array $options = array()
    ) {
        $this->propertyTransformers[$propertyPath] = array(
            'transformer' => $transformer,
            'options'     => $options
        );
    }

    /**
     * Adds an attribute transformer
     *
     * @param string                                                                           $backendType
     * @param \Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface $transformer
     * @param array                                                                            $options
     */
    public function addAttributeTransformer(
        $backendType,
        Property\PropertyTransformerInterface $transformer,
        array $options = array()
    ) {
        $this->attributeTransformers[$backendType] = array(
            'transformer' => $transformer,
            'options'     => $options
        );
    }

    /**
     * Remaps values according to $mapping
     *
     * @param array &$values
     * @param array $mapping
     */
    protected function mapValues(array &$values, array $mapping)
    {
        foreach ($mapping as $oldName => $newName) {
            if ($oldName != $newName && isset($values[$oldName])) {
                $values[$newName] = $values[$oldName];
                unset($values[$oldName]);
            }
        }
    }

    /**
     * Loads a product from the database, or creates if it doesn't exist
     *
     * @param array $values
     *
     * @return ProductInterface
     */
    protected function createOrLoadProduct(array $values)
    {
        $identifierAttribute = $this->attributeCache->getIdentifierAttribute();
        $product = $this->productManager->getImportProduct(
            $this->attributeCache->getAttributes(),
            $identifierAttribute,
            $values[$identifierAttribute->getCode()]
        );

        if (!$product) {
            $product = $this->productManager->createProduct();
        }

        return $product;
    }

    /**
     * Sets the values for the properties
     *
     * @param ProductInterface $product
     * @param array            $values
     *
     * @return array an array of errors
     */
    protected function setPropertyValues(ProductInterface $product, array $values)
    {
        $errors = array();

        foreach ($values as $propertyPath => $value) {
            try {
                $this->propertyAccessor->setValue(
                    $product,
                    $propertyPath,
                    $this->getTransformedValue($value, $this->propertyTransformers[$propertyPath])
                );
            } catch (InvalidValueException $ex) {
                $errors[] = $this->productValidator->getTranslatedExceptionMessage($propertyPath, $ex);
            }
        }

        return array_merge(
            $errors,
            $this->productValidator->validateProductProperties($product, $values)
        );
    }

    /**
     * Sets the default values of the product
     *
     * @param ProductInterface $product
     * @param type             $defaults
     */
    protected function setDefaultValues(ProductInterface $product, $defaults)
    {
        foreach ($defaults as $propertyPath => $value) {
            $this->propertyAccessor->setValue($product, $propertyPath, $value);
        }
    }

    /**
     * Sets the values of the attributes
     *
     * @param ProductInterface $product
     * @param array            $attributeValues
     *
     * @return array an array of errors
     */
    protected function setAttributeValues(ProductInterface $product, array $attributeValues)
    {
        $requiredAttributeCodes = $this->attributeCache->getRequiredAttributeCodes($product);
        $columns = $this->attributeCache->getColumns();
        $errors = array();
        foreach ($attributeValues as $columnCode => $columnValue) {
            $columnInfo = $columns[$columnCode];
            try {
                if ('' != trim($columnValue) || in_array($columnInfo['code'], $requiredAttributeCodes)) {
                    $backendType = $columnInfo['attribute']->getBackendType();
                    $transformerConfig = isset($this->attributeTransformers[$backendType])
                            ? $this->attributeTransformers[$backendType]
                            : $this->attributeTransformers['default'];
                    $value = $this->getTransformedValue($columnValue, $transformerConfig);
                    $this->setAttributeValue($product, $value, $columnInfo, $transformerConfig);
                    $errors = array_merge(
                        $errors,
                        $this->productValidator->validateProductValue(
                            $columnCode,
                            $columnInfo['attribute'],
                            $value
                        )
                    );
                }
            } catch (InvalidValueException $ex) {
                $errors[] = $this->productValidator->getTranslatedExceptionMessage($columnCode, $ex);
            }
        }
        $this->productManager->handleMedia($product);

        return $errors;
    }

    /**
     * Sets the value for a given attribute
     *
     * @param ProductInterface $product
     * @param mixed            $value
     * @param array            $columnInfo
     * @param array            $transformerConfig
     */
    protected function setAttributeValue(
        ProductInterface $product,
        $value,
        array $columnInfo,
        array $transformerConfig = null
    ) {
        $productValue = $product->getValue($columnInfo['code'], $columnInfo['locale'], $columnInfo['scope']);
        if (!$productValue) {
            $productValue = $product->createValue($columnInfo['code'], $columnInfo['locale'], $columnInfo['scope']);
            $product->addValue($productValue);
        }

        if ($transformerConfig && $transformerConfig['transformer'] instanceof Property\ProductValueUpdaterInterface) {
            $transformerConfig['transformer']->updateProductValue($productValue, $value, $transformerConfig['options']);
        } else {
            $productValue->setData($value);
        }
    }

    /**
     * Returns a transformed value
     *
     * @param string $value
     * @param array  $transformerConfig
     *
     * @return type
     */
    protected function getTransformedValue($value, array $transformerConfig)
    {
        return $transformerConfig['transformer']->transform($value, $transformerConfig['options']);
    }
}
