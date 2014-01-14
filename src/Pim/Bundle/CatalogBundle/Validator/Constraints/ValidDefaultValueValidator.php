<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Valid default value validator
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidDefaultValueValidator extends ConstraintValidator
{
    protected $methodMapping = array(
        'pim_catalog_date'             => 'validateDate',
        'pim_catalog_price_collection' => 'validateNumber',
        'pim_catalog_number'           => 'validateNumber',
        'pim_catalog_metric'           => 'validateNumber',
        'pim_catalog_text'             => 'validateText',
        'pim_catalog_textarea'         => 'validateText'
    );

    /**
     * @param mixed      $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if ($entity->getDefaultValue() === null) {
            return;
        }

        $type = $entity->getAttributeType();

        if (isset($this->methodMapping[$type])) {
            $method = $this->methodMapping[$type];
            $this->$method($entity, $constraint);
        }
    }

    /**
     * Validate a date defaultValue
     *
     * @param mixed      $entity
     * @param Constraint $constraint
     */
    protected function validateDate($entity, Constraint $constraint)
    {
        $value = $entity->getDefaultValue();

        if (!$value instanceof \Datetime) {
            $this->context->addViolationAt($constraint->propertyPath, $constraint->dateFormatMessage);

            return;
        }

        if ($min = $entity->getDateMin()) {
            if ($min->getTimestamp() > $value->getTimestamp()) {
                $this->context->addViolationAt($constraint->propertyPath, $constraint->dateMessage);

                return;
            }
        }

        if ($max = $entity->getDateMax()) {
            if ($max->getTimestamp() < $value->getTimestamp()) {
                $this->context->addViolationAt($constraint->propertyPath, $constraint->dateMessage);
            }
        }
    }

    /**
     * Validate a number defaultValue
     *
     * @param mixed      $entity
     * @param Constraint $constraint
     */
    protected function validateNumber($entity, Constraint $constraint)
    {
        $value = $entity->getDefaultValue();

        if ($entity->isNegativeAllowed() === false && $value < 0) {
            $this->context->addViolationAt($constraint->propertyPath, $constraint->negativeMessage);

            return;
        }

        if ($entity->getNumberMin() !== null && $value < $entity->getNumberMin()) {
            $this->context->addViolationAt($constraint->propertyPath, $constraint->numberMessage);

            return;
        }

        if ($entity->getNumberMax() !== null && $value > $entity->getNumberMax()) {
            $this->context->addViolationAt($constraint->propertyPath, $constraint->numberMessage);

            return;
        }

        if ($entity->isDecimalsAllowed() === false && $value != (int) $value) {
            $this->context->addViolationAt($constraint->propertyPath, $constraint->decimalsMessage);
        }
    }

    /**
     * Validate a text defaultValue
     *
     * @param mixed      $entity
     * @param Constraint $constraint
     */
    protected function validateText($entity, Constraint $constraint)
    {
        $value = $entity->getDefaultValue();

        if ($entity->getMaxCharacters() !== null) {
            if (strlen($value) > $entity->getMaxCharacters()) {
                $this->context->addViolationAt($constraint->propertyPath, $constraint->charactersMessage);

                return;
            }
        }

        if ($entity->getAttributeType() === 'pim_catalog_text' && $entity->getValidationRule() == 'regexp') {
            if (@preg_match($entity->getValidationRegexp(), $value)) {
                return;
            }
            $this->context->addViolationAt($constraint->propertyPath, $constraint->regexpMessage);
        }
    }
}
