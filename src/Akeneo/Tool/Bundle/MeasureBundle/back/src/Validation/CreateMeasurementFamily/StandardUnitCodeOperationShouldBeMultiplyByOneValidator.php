<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\CreateMeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StandardUnitCodeOperationShouldBeMultiplyByOneValidator extends ConstraintValidator
{
    /**
     * @param CreateMeasurementFamilyCommand $saveMeasurementFamily
     */
    public function validate($saveMeasurementFamily, Constraint $constraint)
    {
        $standardUnit = $this->standardUnit($saveMeasurementFamily);
        $hasOneOperation = 1 === (is_countable($standardUnit['convert_from_standard']) ? \count($standardUnit['convert_from_standard']) : 0);
        if (!$hasOneOperation) {
            return;
        }
        $hasOperationMultiply = 'mul' === $standardUnit['convert_from_standard'][0]['operator'];
        $hasOperationValueOne = '1' === $standardUnit['convert_from_standard'][0]['value'];
        if (!$hasOperationMultiply || !$hasOperationValueOne) {
            $this->context
                ->buildViolation(StandardUnitCodeOperationShouldBeMultiplyByOne::ERROR_MESSAGE)
                ->setParameter('%measurement_family_code%', $saveMeasurementFamily->code)
                ->atPath('units[0].convert_from_standard')
                ->addViolation();
        }
    }

    /**
     * @param CreateMeasurementFamilyCommand $saveMeasurementFamily
     */
    private function standardUnit($saveMeasurementFamily): array
    {
        foreach ($saveMeasurementFamily->units as $unit) {
            if ($saveMeasurementFamily->standardUnitCode === $unit['code']) {
                return $unit;
            }
        }

        return [];
    }
}
