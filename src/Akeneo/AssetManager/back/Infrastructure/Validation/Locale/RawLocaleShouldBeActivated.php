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

namespace Akeneo\AssetManager\Infrastructure\Validation\Locale;

use Symfony\Component\Validator\Constraint;

class RawLocaleShouldBeActivated extends Constraint
{
    public const ERROR_MESSAGE_SINGULAR = 'Locale "locale_identifier" does not exist or is not activated.';

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return RawLocaleShouldBeActivatedValidator::class;
    }
}
