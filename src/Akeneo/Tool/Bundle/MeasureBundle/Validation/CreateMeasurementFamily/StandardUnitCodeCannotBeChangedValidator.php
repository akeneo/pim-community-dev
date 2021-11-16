<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\CreateMeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyCommand;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StandardUnitCodeCannotBeChangedValidator extends ConstraintValidator
{
    private MeasurementFamilyRepositoryInterface $measurementFamilyRepository;

    public function __construct(MeasurementFamilyRepositoryInterface $measurementFamilyRepository)
    {
        $this->measurementFamilyRepository = $measurementFamilyRepository;
    }

    /**
     * @param CreateMeasurementFamilyCommand $createMeasurementFamilyCommand
     * @inheritDoc
     */
    public function validate($createMeasurementFamilyCommand, Constraint $constraint)
    {
        try {
            $measurementFamily = $this->measurementFamilyRepository
                ->getByCode(MeasurementFamilyCode::fromString($createMeasurementFamilyCommand->code));
        } catch (MeasurementFamilyNotFoundException $e) {
            return;
        }

        if ($createMeasurementFamilyCommand->standardUnitCode !== $this->standardUnitCode($measurementFamily)) {
            $this->context->buildViolation(StandardUnitCodeCannotBeChanged::ERROR_MESSAGE)
                ->setParameter('%measurement_family_code%', $createMeasurementFamilyCommand->code)
                ->atPath('standard_unit_code')
                ->setInvalidValue($createMeasurementFamilyCommand->standardUnitCode)
                ->addViolation();
        }
    }

    private function standardUnitCode(MeasurementFamily $measurementFamily): string
    {
        return $measurementFamily->normalize()['standard_unit_code'];
    }
}
