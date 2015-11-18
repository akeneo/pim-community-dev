<?php

namespace Pim\Component\Localization\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Localized number constraint
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFormatValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($number, Constraint $constraint)
    {
        preg_match('|\d+((?P<decimal>\D+)\d+)?|', $number, $matches);

        if (isset($matches['decimal']) && $matches['decimal'] !== $constraint->decimalSeparator) {
            $violation = $this->context->buildViolation($constraint->message, [
                '{{ decimal_separator }}' => $constraint->decimalSeparator
            ]);
            $violation->atPath($constraint->path);

            $violation->addViolation();
        }
    }
}
