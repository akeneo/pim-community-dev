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

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class LocalesShouldBeActivated extends Constraint
{
    public const ERROR_MESSAGE_SINGULAR = 'Locale "locale_identifier" does not exist or is not activated.';
    public const ERROR_MESSAGE_PLURAL = 'Locales "locale_identifier" do not exist or are not activated.';

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'akeneo_assetmanager.validator.channel.locales_should_be_activated';
    }
}
