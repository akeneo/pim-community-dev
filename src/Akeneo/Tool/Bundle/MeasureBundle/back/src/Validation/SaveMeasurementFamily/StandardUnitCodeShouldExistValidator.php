<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\SaveMeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

class StandardUnitCodeShouldExistValidator extends ConstraintValidator
{
    private const PROPERTY_PATH = 'standard_unit_code';

    public function validate($saveMeasurementFamilyCommand, Constraint $constraint)
    {
        if (!$saveMeasurementFamilyCommand instanceof SaveMeasurementFamilyCommand) {
            throw new \LogicException(
                sprintf(
                    'Expect an instance of class "%s", "%s" given',
                    SaveMeasurementFamilyCommand::class,
                    get_class($saveMeasurementFamilyCommand)
                )
            );
        }
        $standardUnitCode = $saveMeasurementFamilyCommand->standardUnitCode;
        if (empty($standardUnitCode)) {
            $this->context
                ->buildViolation(StandardUnitCodeShouldExist::STANDARD_UNIT_CODE_IS_REQUIRED)
                ->atPath(self::PROPERTY_PATH)
                ->addViolation();

            return;
        }

        $validator = Validation::createValidator();
        $measurementFamilyCode = $saveMeasurementFamilyCommand->code;
        $violations = $validator->validate(
            $saveMeasurementFamilyCommand->units,
            [
                new Callback(
                    function (array $units, ExecutionContextInterface $context) use ($standardUnitCode, $measurementFamilyCode) {
                        foreach ($units as $unit) {
                            if ($standardUnitCode === $unit['code']) {
                                return;
                            }
                        }
                        $context->buildViolation(
                            StandardUnitCodeShouldExist::STANDARD_UNIT_CODE_SHOULD_EXIST_IN_THE_LIST_OF_UNITS,
                            ['%standard_unit_code%' => $standardUnitCode, '%measurement_family_code%' => $measurementFamilyCode]
                        )->addViolation();
                    }
                )
            ]
        );

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->buildViolation($violation->getMessage())
                    ->setParameters($violation->getParameters())
                    ->atPath(self::PROPERTY_PATH)
                    ->setCode($violation->getCode())
                    ->setPlural($violation->getPlural() ?? 0)
                    ->setInvalidValue($violation->getInvalidValue())
                    ->addViolation();
            }
        }
    }
}
