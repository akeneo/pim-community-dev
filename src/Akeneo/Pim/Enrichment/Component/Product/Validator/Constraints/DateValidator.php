<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\DateValidator as BaseDateValidator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DateValidator extends BaseDateValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Date) {
            throw new UnexpectedTypeException($constraint, Date::class);
        }
        if ($value instanceof \DateTimeInterface) {
            return;
        }

        parent::validate($value, $constraint);

        foreach ($this->context->getViolations() as $key => $violation) {
            /* @var ConstraintViolationInterface $violation */
            if (in_array($violation->getCode(), [Date::INVALID_DATE_ERROR, Date::INVALID_FORMAT_ERROR])) {
                $this->context->getViolations()->remove($key);
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%attribute%', $constraint->attributeCode)
                    ->setInvalidValue($value)
                    ->setCode($violation->getCode())
                    ->addViolation();
            }
        }
    }
}
