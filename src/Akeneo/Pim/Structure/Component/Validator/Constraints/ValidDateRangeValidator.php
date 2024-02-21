<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Valid date range validator
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidDateRangeValidator extends ConstraintValidator
{
    /**
     * Validate the date range
     *
     * @param mixed      $entity
     * @param Constraint $constraint
     *
     * @throws \Exception
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof ValidDateRange) {
            throw new UnexpectedTypeException($constraint, ValidDateRange::class);
        }

        $min = $entity->getDateMin();
        $max = $entity->getDateMax();

        if (!$this->isDateValid($min)) {
            $this->context->buildViolation($constraint->invalidDateMessage)
                ->atPath('dateMin')
                ->addViolation();
        }

        if (!$this->isDateValid($max)) {
            $this->context->buildViolation($constraint->invalidDateMessage)
                ->atPath('dateMax')
                ->addViolation();
        }

        if ($min instanceof \DateTime && $max instanceof \DateTime) {
            if ($min->getTimestamp() >= $max->getTimestamp()) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('dateMax')
                    ->addViolation();
            }
        }
    }

    /**
     * Check if the date/time/datetime is valid based on the rules defined in the entity
     *
     * @param mixed $date
     *
     * @return bool
     */
    protected function isDateValid($date)
    {
        if (!$date || $date instanceof \DateTime) {
            return true;
        }

        return false;
    }
}
