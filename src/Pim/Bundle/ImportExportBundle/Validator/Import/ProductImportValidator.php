<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Import;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\ImportExportBundle\Cache\AttributeCache;
use Pim\Bundle\ImportExportBundle\Validator\Import\ImportValidator;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface;

/**
 * Validates an imported product
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductImportValidator extends ImportValidator
{
    /**
     * @var ConstraintGuesserInterface
     */
    protected $constraintGuesser;

    /**
     * @var array
     */
    protected $constraints = array();

    /**
     * Constructor
     *
     * @param ValidatorInterface         $validator
     * @param ConstraintGuesserInterface $constraintGuesser
     */
    public function __construct(
        ValidatorInterface $validator,
        ConstraintGuesserInterface $constraintGuesser
    ) {
        parent::__construct($validator);
        $this->constraintGuesser = $constraintGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, array $columnsInfo, array $data, array $errors = array())
    {
        $this->checkIdentifier($entity, $columnsInfo, $data);

        foreach ($columnsInfo as $columnInfo) {
            if ($columnInfo->getAttribute()) {
                $violations = $this->validateProductValue($entity, $columnInfo);
            } else {
                $violations = $this->validator->validateProperty($entity, $columnInfo->getPropertyPath());
            }

            if ($violations->count()) {
                $errors[$columnInfo->getLabel()] = $this->getErrorArray($violations);
            }
        }

        return $errors;
    }

    /**
     * Validates a ProductValue
     *
     * @param ProductInterface    $product
     * @param ColumnInfoInterface $attribute
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateProductValue(ProductInterface $product, ColumnInfoInterface $columnInfo)
    {
        return $this->validator->validateValue(
            $this->getProductValue($product, $columnInfo)->getData(),
            $this->getAttributeConstraints($columnInfo->getAttribute())
        );
    }

    /**
     * Returns an array of constraints for a given attribute
     *
     * @param ProductAttribute $attribute
     *
     * @return string
     */
    protected function getAttributeConstraints(ProductAttribute $attribute)
    {
        $code = $attribute->getCode();
        if (!isset($this->constraints[$code])) {
            if ($this->constraintGuesser->supportAttribute($attribute)) {
                $this->constraints[$code] = $this->constraintGuesser->guessConstraints($attribute);
            } else {
                $this->constraints[$code] = array();
            }
        }

        return $this->constraints[$code];
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdentifier(array $columnsInfo, $entity)
    {
        $columnLabel = $this->getIdentifierColumn($columnsInfo);
        foreach ($columnsInfo as $columnInfo) {
            if ($columnLabel === $columnInfo->getLabel()) {
                return $this->getProductValue($entity, $columnInfo)->getData();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdentifierColumn(array $columnsInfo)
    {
        foreach ($columnsInfo as $columnInfo) {
            if ($columnInfo->getAttribute() &&
                AttributeCache::IDENTIFIER_ATTRIBUTE_TYPE === $columnInfo->getAttribute()->getAttributeType()) {
                return $columnInfo->getLabel();
            }
        }
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
        return $product->getValue($columnInfo->getName(), $columnInfo->getLocale(), $columnInfo->getScope());
    }
}
