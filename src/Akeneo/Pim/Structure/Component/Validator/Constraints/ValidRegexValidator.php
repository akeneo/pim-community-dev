<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Valid regex validator
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidRegexValidator extends ConstraintValidator
{
    /**
     * Validate regex
     *
     * @param mixed      $value
     * @param Constraint $constraint
     *
     * @throws \Exception
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidRegex) {
            throw new UnexpectedTypeException($constraint, ValidRegex::class);
        }

        if ($value) {
            if (false === @preg_match($value, null)) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}
