<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Measurement;

use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UnitBelongsToMeasurementFamilyValidator extends ConstraintValidator
{
    public function __construct(
        private MeasureManager $measureManager,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($unitCode, Constraint $constraint): void
    {
        if (!$constraint instanceof UnitBelongsToMeasurementFamily) {
            throw new \InvalidArgumentException('Invalid constraint');
        }

        if (!$this->measureManager->familyExists($constraint->measurementFamilyCode)) {
            $this->context->buildViolation(
                UnitBelongsToMeasurementFamily::FAMILY_DOES_NOT_EXIST,
                [
                    '{{ measurement_family_code }}' => $constraint->measurementFamilyCode,
                ],
            )->addViolation();
        }

        if (!$this->measureManager->unitCodeExistsInFamily($unitCode, $constraint->measurementFamilyCode)) {
            $this->context->buildViolation(
                UnitBelongsToMeasurementFamily::UNIT_DOES_NOT_EXIST,
                [
                    '{{ measurement_family_code }}' => $constraint->measurementFamilyCode,
                    '{{ unit_code }}' => $unitCode,
                ],
            )->addViolation();
        }
    }
}
