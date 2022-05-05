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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Measurement;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Measurement\Unit as UnitConstraint;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\FindUnit;
use Akeneo\Tool\Bundle\MeasureBundle\PublicApi\Unit;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class UnitValidator extends ConstraintValidator
{
    public function __construct(private FindUnit $findUnit)
    {
    }

    public function validate($unitCode, Constraint $constraint): void
    {
        if (!$constraint instanceof UnitConstraint) {
            throw new UnexpectedTypeException($constraint, UnitConstraint::class);
        }

        $unit = $this->findUnit->byMeasurementFamilyCodeAndUnitCode($constraint->getFamilyCode(), $unitCode);

        if (!$unit instanceof Unit) {
            $this->context->buildViolation(
                UnitConstraint::UNIT_SHOULD_EXIST,
                [
                    '{{ unit_code }}' => $unitCode,
                    '{{ measurement_family }}' => $constraint->getFamilyCode(),
                ],
            )
            ->addViolation();
        }
    }
}
