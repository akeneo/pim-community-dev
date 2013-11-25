<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Import;

use Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Validates an imported product
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductImportValidator
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var ConstraintGuesserInterface
     */
    protected $constraintGuesser;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var array
     */
    protected $constraints = array();

    /**
     * Constructor
     *
     * @param ValidatorInterface         $validator
     * @param ConstraintGuesserInterface $constraintGuesser
     * @param PropertyAccessorInterface  $propertyAccessor
     */
    public function __construct(
        ValidatorInterface $validator,
        ConstraintGuesserInterface $constraintGuesser,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->validator = $validator;
        $this->constraintGuesser = $constraintGuesser;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * Validates a property
     *
     * @param ProductInterface $product
     * @param string           $propertyPath
     *
     * @return ConstraintViolationList
     */
    public function validateProperty(ProductInterface $product, $propertyPath)
    {
        return $this->validator->validateProperty($product, $propertyPath);
    }

    /**
     * Validates a ProductValue
     *
     * @param  ProductValueInterface            $productValue
     * @param  ProductAttribute                 $attribute
     * @return ConstraintViolationListInterface
     */
    public function validateProductValue(ProductValueInterface $productValue, ProductAttribute $attribute)
    {
        return $this->validator->validateValue(
                $this->propertyAccessor->getValue($productValue, $attribute->getBackendType()),
                $this->getAttributeConstraints($attribute)
        );
    }

    /**
     * Returns an array of constraints for a given attribute
     *
     * @param ProductAttribute $attribute
     *
     * @return string
     */
    public function getAttributeConstraints(ProductAttribute $attribute)
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
}
