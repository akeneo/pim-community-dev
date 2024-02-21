<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\Common;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validation;

class CodeValidator extends ConstraintValidator
{
    private const MAX_CODE_LENGTH = 100;

    public function validate($code, Constraint $constraint)
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate(
            $code,
            [
                new NotBlank(),
                new Type(['type' => 'string']),
                new Length(['max' => self::MAX_CODE_LENGTH]),
                new Regex(
                    [
                        'pattern' => '/^[a-zA-Z0-9_]+$/',
                        'message' => 'pim_measurements.validation.common.code.pattern',
                    ]
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
}
