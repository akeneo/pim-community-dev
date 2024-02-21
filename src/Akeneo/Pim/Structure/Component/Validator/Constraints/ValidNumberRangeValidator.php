<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Valid number range validator
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidNumberRangeValidator extends ConstraintValidator
{
    /**
     * Validate the range
     *
     * @param mixed      $entity
     * @param Constraint $constraint
     *
     * @throws \Exception
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof ValidNumberRange) {
            throw new UnexpectedTypeException($constraint, ValidNumberRange::class);
        }

        $min = $entity->getNumberMin();
        $max = $entity->getNumberMax();
        if ($min && !$this->isNumberValid($entity, $min)) {
            $this->context->buildViolation($constraint->invalidNumberMessage)
                ->atPath('numberMin')
                ->addViolation();
        }

        if ($max && PHP_INT_MAX < $max) {
            $this->context->buildViolation(ValidNumberRange::PHP_INT_MAX_REACHED, [
                '%php_int_max%' => PHP_INT_MAX
            ])->atPath('numberMax')->addViolation();
        } elseif ($max && !$this->isNumberValid($entity, $max)) {
            $this->context->buildViolation($constraint->invalidNumberMessage)
                ->atPath('numberMax')
                ->addViolation();
        }

        if ($min && $max && $min >= $max) {
            $this->context->buildViolation($constraint->message)
                ->atPath('numberMax')
                ->addViolation();
        }
    }

    /**
     * Check if the number is valid based on the rules defined in the entity
     *
     * @param mixed $entity
     * @param int   $number
     *
     * @return bool
     */
    protected function isNumberValid($entity, $number)
    {
        if ($entity->isNegativeAllowed()) {
            if ($number == (int) $number || $entity->isDecimalsAllowed()) {
                return true;
            }
        } elseif (($number == (int) $number || $entity->isDecimalsAllowed()) && $number >= 0) {
            return true;
        }

        return false;
    }
}
