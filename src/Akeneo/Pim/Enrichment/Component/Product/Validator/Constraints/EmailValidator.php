<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\EmailValidator as BaseValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmailValidator extends BaseValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Email) {
            throw new UnexpectedTypeException($constraint, Email::class);
        }

        parent::validate($value, $constraint);

        foreach ($this->context->getViolations() as $key => $violation) {
            if ($violation->getCode() === Email::INVALID_FORMAT_ERROR) {
                $this->context->getViolations()->remove($key);
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%attribute%', $constraint->attributeCode)
                    ->setInvalidValue($value)
                    ->setCode(Email::INVALID_FORMAT_ERROR)
                    ->addViolation();
            }
        }
    }
}
