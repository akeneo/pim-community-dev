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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Symfony\Component\Validator\Constraint;

class ChannelShouldExist extends Constraint
{
    public const NOT_EXIST_MESSAGE = 'akeneo.tailored_export.validation.channel.should_exist';

    public function validatedBy(): string
    {
        return 'akeneo.tailored_export.validation.channel_should_exist';
    }
}
