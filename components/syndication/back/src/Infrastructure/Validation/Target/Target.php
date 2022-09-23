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

namespace Akeneo\Platform\Syndication\Infrastructure\Validation\Target;

use Symfony\Component\Validator\Constraint;

class Target extends Constraint
{
    public const TARGET_SHOULD_NOT_BE_BLANK = 'akeneo.syndication.validation.columns.target.should_not_be_blank';
    public const TARGET_MAX_LENGTH_REACHED = 'akeneo.syndication.validation.columns.target.max_length_reached';
    public const TARGET_NAME_SHOULD_BE_UNIQUE = 'akeneo.syndication.validation.columns.target.should_be_unique';

    public function validatedBy(): string
    {
        return 'akeneo.syndication.validation.target';
    }
}
