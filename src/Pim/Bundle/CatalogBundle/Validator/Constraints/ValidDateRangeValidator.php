<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
     */
    public function validate($entity, Constraint $constraint)
    {
        $min = $entity->getDateMin();
        $max = $entity->getDateMax();

        if (!$this->isDateValid($min)) {
            $this->context->addViolationAt('dateMin', $constraint->invalidDateMessage);
        }

        if (!$this->isDateValid($max)) {
            $this->context->addViolationAt('dateMax', $constraint->invalidDateMessage);
        }

        if ($min instanceof \DateTime && $max instanceof \DateTime) {
            if ($min->getTimestamp() >= $max->getTimestamp()) {
                $this->context->addViolationAt('dateMax', $constraint->message);
            }
        }
    }

    /**
     * Check if the date/time/datetime is valid based on the rules defined in the entity
     * @param mixed $date
     *
     * @return boolean
     */
    protected function isDateValid($date)
    {
        if (!$date || $date instanceof \DateTime) {
            return true;
        }

        return false;
    }
}
