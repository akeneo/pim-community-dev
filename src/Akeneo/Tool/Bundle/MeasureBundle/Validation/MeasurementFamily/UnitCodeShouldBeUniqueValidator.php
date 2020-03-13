<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

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
class UnitCodeShouldBeUniqueValidator extends ConstraintValidator
{
    /** @var GetMeasurementFamilyCodeInterface */
    private $getMeasurementFamilyCode;

    public function __construct(GetMeasurementFamilyCodeInterface $getMeasurementFamilyCode)
    {
        $this->getMeasurementFamilyCode = $getMeasurementFamilyCode;
    }

    public function validate($unitCode, Constraint $constraint)
    {
        try {
            $measurementFamilyCode = $this->getMeasurementFamilyCode->forUnitCode(UnitCode::fromString($unitCode));
        } catch (MeasurementFamilyNotFoundException $e) {
            return;
        }

        $this->context
            ->buildViolation(UnitCodeShouldBeUnique::ERROR_MESSAGE)
            ->setParameters(['%unit_code%' => $unitCode, '%measurement_family_code%' => $measurementFamilyCode->normalize()])
            ->setInvalidValue($unitCode)
            ->addViolation();
    }
}
