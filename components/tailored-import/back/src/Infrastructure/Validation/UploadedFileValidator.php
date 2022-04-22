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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UploadedFileValidator extends ConstraintValidator
{
    public const ALLOWED_MIME_TYPES = [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];
    public const SIZE_LIMIT = '10M';

    public function validate($uploadedFile, Constraint $constraint): void
    {
        if (!$constraint instanceof UploadedFile) {
            throw new UnexpectedTypeException($constraint, UploadedFile::class);
        }

        $validator = $this->context->getValidator();
        // TODO: build the violation without file
        $validator->inContext($this->context)->atPath('[file]')->validate($uploadedFile, [
            new Valid(),
            new File([
                'maxSize' => self::SIZE_LIMIT,
                'maxSizeMessage' => UploadedFile::MAX_SIZE,
                'mimeTypes' => self::ALLOWED_MIME_TYPES,
                'mimeTypesMessage' => UploadedFile::NOT_ALLOWED_MIME_TYPE,
            ]),
        ]);
    }
}
