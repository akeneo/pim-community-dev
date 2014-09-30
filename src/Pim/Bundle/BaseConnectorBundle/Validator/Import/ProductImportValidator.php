<?php

namespace Pim\Bundle\BaseConnectorBundle\Validator\Import;

use Pim\Bundle\BaseConnectorBundle\Exception\DuplicateProductValueException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\TransformBundle\Transformer\ProductTransformer;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

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
     * @var array
     */
    protected $uniqueValues = array();

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
        $this->checkUniqueValues($entity, $columnsInfo, $data);

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
     * Reset the stete of the validator
     */
    public function reset()
    {
        $this->identifiers  = [];
        $this->uniqueValues = [];
    }

    /**
     * Checks the uniqueness of product values that should be unique
     * As the uniqueness check is normally executed against the database
     * and imported products have not been persisted yet, this effectively
     * checks that the items in the current batch don't contain duplicate values
     * for unique attributes.
     *
     * @param object $entity
     * @param array  $columnsInfo
     * @param array  $data
     *
     * @throws DuplicateProductValueException When duplicate values are encountered
     */
    protected function checkUniqueValues($entity, array $columnsInfo, array $data)
    {
        foreach ($columnsInfo as $columnInfo) {
            if ($columnInfo->getAttribute()) {
                $value = $this->getProductValue($entity, $columnInfo);
                if ($value->getAttribute()->isUnique()) {
                    $code      = $value->getAttribute()->getCode();
                    $valueData = (string) $value;
                    if ($valueData !== '') {
                        $this->uniqueValues[$code] =
                            isset($this->uniqueValues[$code]) ? $this->uniqueValues[$code] : [];

                        if (isset($this->uniqueValues[$code][$valueData])) {
                            throw new DuplicateProductValueException($code, $valueData, $data);
                        } else {
                            $this->uniqueValues[$code][$valueData] = "";
                        }
                    }
                }
            }
        }
    }

    /**
     * Validates a ProductValue
     *
     * @param ProductInterface    $product
     * @param ColumnInfoInterface $columnInfo
     *
     * @return ConstraintViolationListInterface
     */
    protected function validateProductValue(ProductInterface $product, ColumnInfoInterface $columnInfo)
    {
        $value = $this->getProductValue($product, $columnInfo);
        if (!$value) {
            return new \Symfony\Component\Validator\ConstraintViolationList();
        }

        return $this->validator->validateValue(
            $value->getData(),
            $this->getAttributeConstraints($columnInfo->getAttribute())
        );
    }

    /**
     * Returns an array of constraints for a given attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return string
     */
    protected function getAttributeConstraints(AttributeInterface $attribute)
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
        $identifier = null;
        foreach ($columnsInfo as $columnInfo) {
            if ($columnLabel === $columnInfo->getLabel()) {
                $identifier = $this->getProductValue($entity, $columnInfo)->getData();
                break;
            }
        }

        return $identifier;
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdentifierColumn(array $columnsInfo)
    {
        $label = null;
        foreach ($columnsInfo as $columnInfo) {
            if ($columnInfo->getAttribute() &&
                ProductTransformer::IDENTIFIER_ATTRIBUTE_TYPE === $columnInfo->getAttribute()->getAttributeType()) {
                $label = $columnInfo->getLabel();
                break;
            }
        }

        return $label;
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
