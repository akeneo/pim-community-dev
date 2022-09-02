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

class IsValidFileTemplateInformationQuery extends Constraint
{
    public const FILE_NOT_FOUND = 'akeneo.tailored_import.validation.file_not_found';
    public const SHEET_NOT_FOUND = 'akeneo.tailored_import.validation.sheet_not_found';
}
