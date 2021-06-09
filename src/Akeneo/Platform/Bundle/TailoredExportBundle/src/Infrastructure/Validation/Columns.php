<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

class Columns extends Constraint
{
    public const TARGET_SHOULD_NOT_BE_BLANK = 'akeneo.tailored_export.validation.columns.target.should_not_be_blank';
    public const TARGET_MAX_LENGTH_REACHED = 'akeneo.tailored_export.validation.columns.target.max_length_reached';
    public const TARGET_NAME_SHOULD_BE_UNIQUE = 'akeneo.tailored_export.validation.columns.target.should_be_unique';
}
