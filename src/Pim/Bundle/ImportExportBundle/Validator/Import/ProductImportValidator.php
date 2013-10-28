<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Import;

use Symfony\Component\Validator\Validator;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

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
    protected $validator;
    protected $constraintGuesser;
    protected $translator;
    protected $constraints = array();

    public function __construct(
        Validator $validator,
        ConstraintGuesserInterface $constraintGuesser,
        TranslatorInterface $translator
    ) {
        $this->validator = $validator;
        $this->constraintGuesser = $constraintGuesser;
        $this->translator = $translator;
    }
    public function validateProductProperties(ProductInterface $product, array $values)
    {
        $errors = array();
        foreach($values as $propertyPath => $value) {
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
    public function getAttributeConstraints(ProductAttribute $attribute) {
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

    public function getErrors($propertyPath, ConstraintViolationList $violations)
    {
        $errors = array();
        foreach ($violations as $violation) {
            $errors[] = $this->getTranslatedErrorMessage(
                $propertyPath,
                $violation->getMessageTemplate(),
                $violation->getMessageParameters());
        }

        return $errors;
    }

    public function getTranslatedErrorMessage($propertyPath, $message, array $parameters=array())
    {
        return sprintf(
            '%s: %s',
            $propertyPath,
            $this->translator->trans($message, $parameters)
        );
    }
    public function getTranslatedExceptionMessage($propertyPath, InvalidValueException $exception)
    {
        return $this->getTranslatedErrorMessage(
            $propertyPath,
            $exception->getRawMessage(),
            $exception->getMessageParameters()
        );
    }
}
