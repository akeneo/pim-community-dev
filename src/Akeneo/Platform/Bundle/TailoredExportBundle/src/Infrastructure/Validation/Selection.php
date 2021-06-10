<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

class Selection extends Constraint
{
    public const SELECTION_LOCALE_SHOULD_NOT_BE_BLANK = 'akeneo.tailored_export.validation.selection.locale.should_not_be_blank';
}
