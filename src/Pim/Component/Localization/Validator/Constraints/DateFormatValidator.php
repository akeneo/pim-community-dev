<?php

namespace Pim\Component\Localization\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Localized date constraint
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFormatValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($date, Constraint $constraint)
    {
        $datetime = new \DateTime();
        $datetime = $datetime->createFromFormat($constraint->dateFormat, $date);

        if (false === $datetime) {
            $violation = $this->context->buildViolation($constraint->message, [
                '{{ date_format }}' => $constraint->dateFormat
            ]);
            $violation->atPath($constraint->path);

            $violation->addViolation();
        }
    }
}
