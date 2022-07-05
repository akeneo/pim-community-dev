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

class UploadedFile extends Constraint
{
    public const NOT_ALLOWED_MIME_TYPE = 'akeneo.tailored_import.validation.uploaded_file.not_allowed_mime_type';
    public const NOT_ALLOWED_EXTENSION = 'akeneo.tailored_import.validation.uploaded_file.not_allowed_extension';
    public const MAX_SIZE = 'akeneo.tailored_import.validation.uploaded_file.max_size';
}
