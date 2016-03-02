<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
     */
    public function validate($entity, Constraint $constraint)
    {
        $min = $entity->getNumberMin();
        $max = $entity->getNumberMax();

        if ($min && !$this->isNumberValid($entity, $min)) {
            $this->context->buildViolation($constraint->invalidNumberMessage)
                ->atPath('numberMin')
                ->addViolation();
        }

        if ($max && !$this->isNumberValid($entity, $max)) {
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
