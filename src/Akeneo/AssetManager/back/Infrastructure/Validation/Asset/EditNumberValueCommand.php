<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class EditNumberValueCommand extends Constraint
{
    public const NUMBER_SHOULD_BE_STRING = 'pim_asset_manager.asset.validation.number.should_be_string';
    public const NUMBER_SHOULD_BE_INTEGER = 'pim_asset_manager.asset.validation.number.should_be_integer';
    public const INTEGER_TOO_LONG = 'pim_asset_manager.asset.validation.number.integer_too_long';
    public const NUMBER_SHOULD_BE_NUMERIC = 'pim_asset_manager.asset.validation.number.should_be_numeric';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
