<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FilePathValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof FilePath) {
            throw new UnexpectedTypeException($constraint, FilePath::class);
        }

        if (null === $value) {
            return;
        }

        $this->validateFileExtension($value, $constraint->getSupportedFileExtensions());
    }

    private function validateFileExtension(string $filePath, array $supportedFileExtensions): void
    {
        if (empty($supportedFileExtensions)) {
            return;
        }

        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (!in_array($fileExtension, $supportedFileExtensions)) {
            $this->context->addViolation(
                FilePath::UNSUPPORTED_EXTENSION,
                [
                    '{{ supported_extensions }}' => implode(', ', $supportedFileExtensions),
                ],
            );
        }

        if (preg_match('#\p{C}+#u', $filePath)) {
            $this->context->addViolation(FilePath::NON_PRINTABLE_FILE_PATH);
        }
    }
}
