<?php

namespace Akeneo\Platform\JobAutomation\Infrastructure\Validation;

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

        $this->validateFileExtension($value, $constraint->getAllowedFileExtensions());
    }

    private function validateFileExtension(string $filePath, array $allowedFileExtensions): void
    {
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (!in_array($fileExtension, $allowedFileExtensions)) {
            $this->context->addViolation(
                FilePath::BAD_EXTENSION,
                [
                    '{{ allowed_extensions }}' => implode(', ', $allowedFileExtensions),
                ],
            );
        }
    }
}
