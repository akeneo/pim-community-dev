<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\ImportExportBundle\Cache\AttributeCache;
use Pim\Bundle\ImportExportBundle\Exception\UnknownColumnException;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\ImportExportBundle\Validator\Import\ProductImportValidator;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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
     * @var ProductImportValidator
     */
    protected $productValidator;

    /**
     * @var AttributeCache
     */
    protected $attributeCache;

    /**
     * @var string
     */
    protected $labelsHash;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var Attribute 
     */
    protected $identifierAttribute;
    
    /**
     * @var array
     */
    protected $propertyColumns;
            
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        LabelTransformerInterface $labelTransformer,
        TranslatorInterface $translator,
        ProductManager $productManager,
        ProductImportValidator $productValidator,
        AttributeCache $attributeCache
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $labelTransformer, $translator);
        $this->productManager = $productManager;
        $this->productValidator = $productValidator;
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
        $this->initializeAttributes(array_keys($data));
        return $this->doTransform($this->productManager->getFlexibleName(), $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntity($class, array $data)
    {
        $product = $this->productManager->getImportProduct(
            $this->attributes,
            $this->identifierAttribute,
            $data[$this->identifierAttribute->getCode()]
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
        $errors = array();

        foreach ($this->propertyColumns as $label) {
            $columnInfo = $this->getColumnInfo($class, $label);
            $transformerInfo = $this->getTransformerInfo($class, $columnInfo);
            if ($transformerInfo) {
                $errors = array_merge(
                    $errors,
                    $this->setProperty($entity, $columnInfo, $transformerInfo, $data[$label])
                );
            }
            unset($data[$label]);
        }

        $requiredAttributeCodes = $this->attributeCache->getRequiredAttributeCodes($entity);
        foreach ($data as $label => $value) {
            $columnInfo = $this->getColumnInfo($class, $label);
            $attribute = $this->attributes[$columnInfo['name']];
            $columnInfo['propertyPath'] = $attribute->getBackendType();
            $transformerInfo = $this->getTransformerInfo($flexibleValueClass, $columnInfo);
            if ('' != trim($value) || in_array($columnInfo['name'], $requiredAttributeCodes)) {
                $errors = array_merge(
                    $errors,
                    $this->setProductValue($entity, $attribute, $data, $transformerInfo, $value)
                );
            }
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    protected function setProperty($entity, array $columnInfo, array $transformerInfo, $value)
    {
        $errors = parent::setProperty($entity, $columnInfo, $transformerInfo, $value);
        if (!count($errors)) {
            $errors = array_merge(
                $errors,
                $this->getViolationListErrors(
                    $columnInfo['label'],
                    $this->productValidator->validateProperty($entity, $columnInfo['propertyPath'])
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setProductValue($product, $attribute, array $columnInfo, array $transformerInfo, $value)
    {
        $productValue = $this->getProductValue($product, $columnInfo);
        //TODO: check scope and locale
        $errors = parent::setProperty($productValue, $columnInfo, $transformerInfo, $value);
        if (!count($errors)) {
            $errors = array_merge(
                $errors,
                $this->getViolationListErrors(
                    $columnInfo['label'],
                    $this->productValidator->validateProductValue($productValue, $attribute)
                )
            );
        }
    }

    protected function getProductValue($product,array $columnInfo)
    {
        $productValue = $product->getValue($columnInfo['name'], $columnInfo['locale'], $columnInfo['scope']);
        if (!$productValue) {
            $productValue = $product->createValue($columnInfo['code'], $columnInfo['locale'], $columnInfo['scope']);
            $product->addValue($productValue);
        }

        return $productValue;
    }

    /**
     * Returns an array of error strings for a list of violations
     *
     * @param string                           $propertyPath
     * @param ConstraintViolationListInterface $violations
     *
     * @return array
     */
    public function getViolationListErrors($propertyPath, ConstraintViolationListInterface $violations)
    {
        $errors = array();
        foreach ($violations as $violation) {
            $errors[] = $this->getTranslatedErrorMessage(
                $propertyPath,
                $violation->getMessageTemplate(),
                $violation->getMessageParameters()
            );
        }

        return $errors;
    }

    protected function initializeAttributes($labels)
    {
        $hash = md5(serialize($labels));
        if ($hash === $this->labelsHash) {
            return;
        }

        $class = $this->productManager->getFlexibleName();

        $columnInfos = array_map(
            function ($label) use ($class) {
                return $this->getColumnInfo($class, $label);
            },
            $labels
        );

        $metadata = $this->doctrine->getManager()->getClassMetadata($class);
        $codes = array();
        $this->propertyColumns = array();
        foreach($columnInfos as $columnInfo) {
            if ($metadata->hasField($columnInfo['propertyPath'])) {
                $this->propertyColumns[] = $columnInfo['label'];
            } else {
                $codes[] = $columnInfo['name'];
            }            
        }
        $this->attributes = array();
        foreach($this->attributeCache->getAttributes($codes) as $attribute) {
            $this->attributes[$attribute->getCode()] = $attribute;
            if (self::IDENTIFIER_ATTRIBUTE_TYPE === $attribute->getAttributeType()) {
                $this->identifierAttribute = $attribute;
            }
        }

        if (count($this->attributes) != count($codes)) {
            throw new UnknownColumnException(array_diff($codes, array_keys($this->attributes)));
        }
    }
}
