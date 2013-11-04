<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Import;

use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

use Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\ImportExportBundle\Exception\InvalidValueException;

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
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var array
     */
    protected $constraints = array();

    /**
     * Constructor
     *
     * @param ValidatorInterface         $validator
     * @param ConstraintGuesserInterface $constraintGuesser
     * @param TranslatorInterface        $translator
     */
    public function __construct(
        ValidatorInterface $validator,
        ConstraintGuesserInterface $constraintGuesser,
        TranslatorInterface $translator
    ) {
        $this->validator = $validator;
        $this->constraintGuesser = $constraintGuesser;
        $this->translator = $translator;
    }

    /**
     * Validates a list of property
     *
     * @param ProductInterface $product
     * @param array            $values
     *
     * @return array an array of errors
     */
    public function validateProductProperties(ProductInterface $product, array $values)
    {
        $errors = array();
        foreach ($values as $propertyPath => $value) {
            $errors = array_merge(
                $errors,
                $this->getErrors(
                    $propertyPath,
                    $this->validator->validatePropertyValue($product, $propertyPath, $value)
                )
            );
        }

        return $errors;
    }

    /**
     * Validates a ProductValue
     *
     * @param string           $propertyPath
     * @param ProductAttribute $attribute
     * @param mixed            $value
     *
     * @return array an array of errors
     */
    public function validateProductValue($propertyPath, ProductAttribute $attribute, $value)
    {
        return $this->getErrors(
            $propertyPath,
            $this->validator->validateValue(
                $value,
                $this->getAttributeConstraints($attribute)
            )
        );
    }

    /**
     * Returns an array of constraints for a given attribute
     *
     * @param Entity\ProductAttribute $attribute
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

    /**
     * Returns an array of error strings
     *
     * @param string                           $propertyPath
     * @param ConstraintViolationListInterface $violations
     *
     * @return array
     */
    public function getErrors($propertyPath, ConstraintViolationListInterface $violations)
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

    /**
     * Returns a translated error message
     *
     * @param string $propertyPath
     * @param string $message
     * @param array  $parameters
     *
     * @return string
     */
    public function getTranslatedErrorMessage($propertyPath, $message, array $parameters = array())
    {
        return sprintf(
            '%s: %s',
            $propertyPath,
            $this->translator->trans($message, $parameters)
        );
    }

    /**
     * Returns a translated InvalidValueException message
     *
     * @param string                $propertyPath
     * @param InvalidValueException $exception
     *
     * @return string
     */
    public function getTranslatedExceptionMessage($propertyPath, InvalidValueException $exception)
    {
        return $this->getTranslatedErrorMessage(
            $propertyPath,
            $exception->getRawMessage(),
            $exception->getMessageParameters()
        );
    }
}
