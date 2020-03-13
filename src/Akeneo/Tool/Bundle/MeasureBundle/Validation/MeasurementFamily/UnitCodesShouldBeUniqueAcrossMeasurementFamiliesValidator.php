<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\GetMeasurementFamilyCodeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnitCodesShouldBeUniqueAcrossMeasurementFamiliesValidator extends ConstraintValidator
{
    /** @var GetMeasurementFamilyCodeInterface */
    private $getMeasurementFamilyCode;

    public function __construct(GetMeasurementFamilyCodeInterface $getMeasurementFamilyCode)
    {
        $this->getMeasurementFamilyCode = $getMeasurementFamilyCode;
    }

    /**
     * @param SaveMeasurementFamilyCommand $saveMeasurementFamilyCode
     */
    public function validate($saveMeasurementFamilyCode, Constraint $constraint)
    {
        foreach ($saveMeasurementFamilyCode->units as $i => $unit) {
            try {
                $unitCode = $unit['code'];
                $measurementFamilyCode = $this->getMeasurementFamilyCode->forUnitCode(UnitCode::fromString($unitCode));
            } catch (MeasurementFamilyNotFoundException $e) {
                continue;
            }

            if ($measurementFamilyCode->normalize() === $saveMeasurementFamilyCode->code) {
                break;
            }

            $this->context
                ->buildViolation(UnitCodesShouldBeUniqueAcrossMeasurementFamilies::ERROR_MESSAGE)
                ->setParameters(['%unit_code%' => $unitCode, '%measurement_family_code%' => $measurementFamilyCode->normalize()])
                ->setInvalidValue($unitCode)
                ->atPath(sprintf('units[%d][code]', $i))
                ->addViolation();
        }
    }
}
