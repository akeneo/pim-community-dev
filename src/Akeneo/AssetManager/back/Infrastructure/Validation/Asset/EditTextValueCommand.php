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

namespace Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class EditTextValueCommand extends Constraint
{
    public const TEXT_INCOMPATIBLE_WITH_REGULAR_EXPRESSION = 'pim_asset_manager.asset.validation.text.incompatible_with_regular_expression';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
