<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ShouldNotContainDuplicatedUnitsValidator extends ConstraintValidator
{
    public function validate($units, Constraint $constraint)
    {
        $unitCodes = $this->unitCodes($units);
        if ($this->hasDuplicates($unitCodes)) {
            $this->context
                ->buildViolation(ShouldNotContainDuplicatedUnits::SHOULD_NOT_CONTAIN_DUPLICATED_UNITS)
                ->addViolation();
        }
    }

    private function unitCodes(array $units): array
    {
        return array_map(
            static fn (array $unit) => $unit['code'],
            $units
        );
    }

    private function hasDuplicates(array $unitCodes): bool
    {
        return \count($unitCodes) !== \count(array_unique($unitCodes));
    }
}
