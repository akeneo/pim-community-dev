<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Akeneo\Platform\TailoredImport\Domain\Model\Filesystem\Storage;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FileKeyValidator extends ConstraintValidator
{
    public function __construct(
        private FilesystemProvider $fileStorage,
    ) {
    }

    public function validate($fileKey, Constraint $constraint): void
    {
        if (!$constraint instanceof FileKey) {
            throw new UnexpectedTypeException($constraint, FileKey::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($fileKey, [
            new Type('string'),
            new NotBlank(['allowNull' => true]),
        ]);

        if (null === $fileKey || 0 < $this->context->getViolations()->count()) {
            return;
        }

        $this->validateFileExists($fileKey);
    }

    private function validateFileExists(string $fileKey): void
    {
        if (!$this->fileStorage->getFilesystem(Storage::FILE_STORAGE_ALIAS)->fileExists($fileKey)) {
            $this->context->buildViolation(FileKey::FILE_DOES_NOT_EXIST)->addViolation();
        }
    }
}
