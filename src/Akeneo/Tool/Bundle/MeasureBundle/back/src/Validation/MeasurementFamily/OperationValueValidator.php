<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;

class OperationValueValidator extends ConstraintValidator
{
    public function validate($convertValue, Constraint $constraint)
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $convertValue,
            [
                new NotBlank(),
                new Callback(
                    function ($value, ExecutionContextInterface $context) {
                        if (null !== $value && '' !== $value && !$this->isStringNumericWithoutScientificNotation($value)) {
                            $context->buildViolation(OperationValue::VALUE_SHOULD_BE_A_NUMBER_IN_A_STRING)
                                ->addViolation();
                        }
                    }
                ),
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

    private function isStringNumericWithoutScientificNotation($value)
    {
        return is_string($value) && preg_match('~^\d*\.?\d+$~', $value) === 1;
    }
}
