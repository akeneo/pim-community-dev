<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Application\SaveMeasurementFamily\SaveMeasurementFamilyCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

class StandardUnitCodeValidator extends ConstraintValidator
{
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

        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $saveMeasurementFamilyCommand->units,
            [
                new Callback(
                    function (array $units, ExecutionContextInterface $context) use ($standardUnitCode) {
                        foreach ($units as $unit) {
                            if ($standardUnitCode === $unit['code']) {
                                return;
                            }
                        }
                        $context->buildViolation(
                            StandardUnitCode::STANDARD_UNIT_CODE_SHOULD_EXIST,
                            ['%standard_unit_code%' => $standardUnitCode]
                        )->addViolation();
                    }
                )
            ]
        );

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                );
            }
        }
    }
}
