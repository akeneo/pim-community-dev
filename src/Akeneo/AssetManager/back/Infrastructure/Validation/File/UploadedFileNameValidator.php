<?php

namespace Akeneo\AssetManager\Infrastructure\Validation\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UploadedFileNameValidator extends ConstraintValidator
{
    public function validate($uploadedFile, Constraint $constraint)
    {
        if (!$constraint instanceof UploadedFileName) {
            throw new UnexpectedTypeException($constraint, UploadedFileName::class);
        }

        if (!$uploadedFile instanceof UploadedFile) {
            throw new UnexpectedValueException($constraint, UploadedFile::class);
        }

        if (str_contains($uploadedFile->getClientOriginalName(), '­')) {
            $this->context->buildViolation(UploadedFileName::ERROR_MESSAGE)
                ->setParameter('%file_path%', $uploadedFile->getClientOriginalName())
                ->addViolation();
        }
    }
}
