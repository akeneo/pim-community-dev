<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage\None;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\FilePath;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage\None\NoneStorage as NoneStorageConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoneStorageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoneStorageConstraint) {
            throw new UnexpectedTypeException($constraint, NoneStorageConstraint::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'type' => new EqualTo(NoneStorage::TYPE),
                'file_path' => new Optional(new FilePath($constraint->getFilePathSupportedFileExtensions())),
                // TODO RAB-665: These are specifically for quick export, we should investigate to find a proper way
                'filePath' => new Optional(new FilePath($constraint->getFilePathSupportedFileExtensions())),
                'filePathProduct' => new Optional(new FilePath($constraint->getFilePathSupportedFileExtensions())),
                'filePathProductModel' => new Optional(new FilePath($constraint->getFilePathSupportedFileExtensions())),
            ],
        ]));
    }
}
