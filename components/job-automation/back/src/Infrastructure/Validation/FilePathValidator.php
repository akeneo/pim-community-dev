<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FilePathValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof FilePath) {
            throw new UnexpectedTypeException($constraint, FilePath::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, [
            new NotBlank(),
            new Regex([
                'pattern' => '/.\.xlsx$/',
                'message' => FilePath::BAD_EXTENSION,
            ]),
        ]);
    }
}
