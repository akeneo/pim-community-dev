<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\MeasurementUnitExists;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class MeasurementUnitShouldExistValidator extends ConstraintValidator
{
    public function __construct(private MeasurementUnitExists $measurementUnitExists)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, MeasurementUnitShouldExist::class);
        $measurementFamilyCode = $value[$constraint->measurementFamilyCodeKey] ?? null;
        $measurementUnitCode = $value[$constraint->measurementUnitCodeKey] ?? null;
        if (!is_string($measurementFamilyCode) || !is_string($measurementUnitCode)) {
            return;
        }

        if (!$this->measurementUnitExists->inFamily($measurementFamilyCode, $measurementUnitCode)) {
            $this->context
                ->buildViolation($constraint->message, [
                    '{{ measurement_family_code }}' => $measurementFamilyCode,
                    '{{ measurement_unit_code }}' => $measurementUnitCode,
                ])
                ->addViolation();
        }
    }
}
