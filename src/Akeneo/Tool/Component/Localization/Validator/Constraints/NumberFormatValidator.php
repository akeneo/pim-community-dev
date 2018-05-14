<?php

namespace Akeneo\Tool\Component\Localization\Validator\Constraints;

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
    /** @var array */
    protected $decimalSeparators;

    /**
     * @param array $decimalSeparators
     */
    public function __construct(array $decimalSeparators)
    {
        $this->decimalSeparators = $decimalSeparators;
    }

    /**
     * Returns the message for the constraint translation.
     *
     * @param Constraint $constraint
     *
     * @return mixed
     */
    public function getMessage(Constraint $constraint)
    {
        if (isset($this->decimalSeparators[$constraint->decimalSeparator])) {
            return str_replace(
                '{{ decimal_separator }}',
                $this->decimalSeparators[$constraint->decimalSeparator],
                $constraint->message
            );
        } else {
            return $constraint->message;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate($number, Constraint $constraint)
    {
        preg_match('|\d+((?P<decimal>\D+)\d+)?|', $number, $matches);

        if (isset($matches['decimal']) && $matches['decimal'] !== $constraint->decimalSeparator) {
            $violation = $this->context->buildViolation(
                $this->getMessage($constraint),
                ['{{ decimal_separator }}' => $constraint->decimalSeparator]
            );

            $violation->atPath($constraint->path);

            $violation->addViolation();
        }
    }
}
