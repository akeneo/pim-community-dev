<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\FileStorageBundle\Validator\Constraints;

use Symfony\Component\HttpFoundation\File;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class UploadedFileValidator extends ConstraintValidator
{
    /**
     * @param File\UploadedFile $uploadedFile
     * @param UploadedFile $constraint
     */
    public function validate($uploadedFile, Constraint $constraint): void
    {
        if (!$constraint instanceof UploadedFile) {
            throw new UnexpectedTypeException($constraint, UploadedFile::class);
        }

        if ($uploadedFile === null) {
            return;
        }

        if (!$uploadedFile instanceof File\UploadedFile) {
            throw new UnexpectedValueException($uploadedFile, File\UploadedFile::class);
        }

        $allowedTypes = $constraint->types;
        $allowedMimeTypes = array_merge(...array_values($allowedTypes));

        $guessedExtension = strtolower($uploadedFile->guessExtension() ?? '');
        $originalExtension = strtolower($uploadedFile->getClientOriginalExtension());

        $guessedMimeType = strtolower($uploadedFile->getMimeType() ?? '');
        $originalMimeType = strtolower($uploadedFile->getClientMimeType());

        if (!array_key_exists($originalExtension, $allowedTypes)) {
            $this->context->buildViolation($constraint->unsupportedExtensionMessage)
                ->setParameter('{{ extension }}', $originalExtension)
                ->setParameter('{{ extensions }}', implode(', ', array_keys($allowedTypes)))
                ->addViolation()
            ;
            return;
        }

        if (!array_key_exists($guessedExtension, $allowedTypes)) {
            $this->context->buildViolation($constraint->fileIsCorruptedMessage)
                ->addViolation()
            ;
            return;
        }

        if (!in_array($originalMimeType, $allowedMimeTypes, true)) {
            $this->context->buildViolation($constraint->fileIsCorruptedMessage)
                ->addViolation()
            ;
            return;
        }

        if (!in_array($guessedMimeType, $allowedMimeTypes, true)) {
            $this->context->buildViolation($constraint->fileIsCorruptedMessage)
                ->addViolation()
            ;
        }
    }
}
