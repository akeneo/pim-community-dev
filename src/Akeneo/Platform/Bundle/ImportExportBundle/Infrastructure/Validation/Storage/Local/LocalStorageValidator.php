<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage\Local;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\FilePath;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage\Local\LocalStorage as LocalStorageConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LocalStorageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof LocalStorageConstraint) {
            throw new UnexpectedTypeException($constraint, LocalStorageConstraint::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'type' => new EqualTo(LocalStorage::TYPE),
                'file_path' => new Optional(new FilePath($constraint->getFilePathSupportedFileExtensions())),
                'filePathProduct' => new Optional(new FilePath($constraint->getFilePathSupportedFileExtensions())),
                'filePathProductModel' => new Optional(new FilePath($constraint->getFilePathSupportedFileExtensions())),
            ],
        ]));
    }
}
