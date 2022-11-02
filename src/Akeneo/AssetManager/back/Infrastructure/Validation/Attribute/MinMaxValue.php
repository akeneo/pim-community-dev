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

namespace Akeneo\AssetManager\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class MinMaxValue extends Constraint
{
    public const MESSAGE_SHOULD_BE_A_NUMBER = 'pim_asset_manager.attribute.validation.min_value.should_be_a_number';
    public const MESSAGE_MIN_CANNOT_BE_GREATER_THAN_MAX = 'pim_asset_manager.attribute.validation.min_value.cannot_be_greater_than_max';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
