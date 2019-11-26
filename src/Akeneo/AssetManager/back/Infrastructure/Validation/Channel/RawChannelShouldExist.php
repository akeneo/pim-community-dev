<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\Channel;

use Symfony\Component\Validator\Constraint;

class RawChannelShouldExist extends Constraint
{
    public const ERROR_MESSAGE = 'Channel "channel_identifier" does not exist.';

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy()
    {
        return RawChannelShouldExistValidator::class;
    }
}
