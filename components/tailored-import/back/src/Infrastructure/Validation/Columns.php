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

class Columns extends Constraint
{
    public const MAX_COUNT_REACHED = 'akeneo.tailored_import.validation.columns.max_count_reached';
    public const UUID_SHOULD_BE_UNIQUE = 'akeneo.tailored_import.validation.columns.uuid.should_be_unique';
    public const INDEX_SHOULD_BE_UNIQUE = 'akeneo.tailored_import.validation.columns.index.should_be_unique';
    public const LABEL_SHOULD_NOT_BE_BLANK = 'akeneo.tailored_import.validation.columns.label.should_not_be_blank';
    public const LABEL_MAX_LENGTH_REACHED = 'akeneo.tailored_import.validation.columns.label.max_length_reached';
}
