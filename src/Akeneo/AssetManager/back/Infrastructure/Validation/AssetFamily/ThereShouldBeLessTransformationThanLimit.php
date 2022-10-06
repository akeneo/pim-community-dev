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

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily;

use Symfony\Component\Validator\Constraint;

class ThereShouldBeLessTransformationThanLimit extends Constraint
{
    public const ERROR_MESSAGE = 'pim_asset_manager.asset_family.validation.transformation.limit_reached';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return ThereShouldBeLessTransformationThanLimitValidator::class;
    }
}
