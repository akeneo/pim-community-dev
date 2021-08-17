<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Format;

use Symfony\Component\Validator\Constraint;

class Format extends Constraint
{
    public const MAX_TEXT_COUNT_REACHED = 'akeneo.tailored_export.validation.concatenation.max_text_count_reached';
}
