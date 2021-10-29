<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\CreateMeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Application\CreateMeasurementFamily\CreateMeasurementFamilyCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StandardUnitCodeShouldExistValidator extends ConstraintValidator
{
    private const PROPERTY_PATH = 'standard_unit_code';

    public function validate($createMeasurementFamilyCommand, Constraint $constraint)
    {
        if (!$createMeasurementFamilyCommand instanceof CreateMeasurementFamilyCommand) {
            throw new \LogicException(
                sprintf(
                    'Expect an instance of class "%s", "%s" given',
                    CreateMeasurementFamilyCommand::class,
                    get_class($createMeasurementFamilyCommand)
                )
            );
        }
        $standardUnitCode = $createMeasurementFamilyCommand->standardUnitCode;
        if (empty($standardUnitCode)) {
            $this->context
                ->buildViolation(StandardUnitCodeShouldExist::STANDARD_UNIT_CODE_IS_REQUIRED)
                ->atPath(self::PROPERTY_PATH)
                ->addViolation();

            return;
        }

        $validator = Validation::createValidator();
        $measurementFamilyCode = $createMeasurementFamilyCommand->code;
        $violations = $validator->validate(
            $createMeasurementFamilyCommand->units,
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
                /** @var ConstraintViolationInterface $violation */
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
