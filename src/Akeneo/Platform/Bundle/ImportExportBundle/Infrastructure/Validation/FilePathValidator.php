<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
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
        ]);

        if (0 < $this->context->getViolations()->count()) {
            return;
        }

        $this->validateFileExtension($value, $constraint->getSupportedFileExtensions());
    }

    private function validateFileExtension(string $filePath, array $supportedFileExtensions): void
    {
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (!in_array($fileExtension, $supportedFileExtensions)) {
            $this->context->addViolation(
                FilePath::UNSUPPORTED_EXTENSION,
                [
                    '{{ supported_extensions }}' => implode(', ', $supportedFileExtensions),
                ],
            );
        }
    }
}
