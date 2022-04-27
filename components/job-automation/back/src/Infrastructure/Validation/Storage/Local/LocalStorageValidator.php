<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage\Local;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\FilePath;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LocalStorageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof LocalStorage) {
            throw new UnexpectedTypeException($constraint, LocalStorage::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'type' => new EqualTo('local'),
                'file_path' => new FilePath($constraint->getFilePathSupportedFileExtensions()),
            ],
        ]));
    }
}
