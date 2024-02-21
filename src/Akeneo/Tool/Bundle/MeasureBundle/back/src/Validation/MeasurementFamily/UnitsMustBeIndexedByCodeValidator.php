<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnitsMustBeIndexedByCodeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UnitsMustBeIndexedByCode) {
            throw new UnexpectedTypeException($constraint, UnitsMustBeIndexedByCode::class);
        }

        if (!is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        foreach ($value as $key => $unit) {
            if ($key !== $unit['code']) {
                $path = sprintf('[%s]', $key);

                // @see https://github.com/akeneo/pim-community-dev/blob/e166444691b7d63957cef3cac5277a74e4058ce9/src/Akeneo/Tool/Component/Api/Normalizer/Exception/ViolationNormalizer.php#L140
                $constraint->payload['standardPropertyName'] = $this->context->getPropertyPath() . $path;

                $this->context->buildViolation($constraint->message)
                    ->atPath($path)
                    ->setInvalidValue($value)
                    ->addViolation();
            }
        }
    }
}
