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

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class AttributeAsMainMedia extends Constraint
{
    public const ATTRIBUTE_NOT_FOUND = 'pim_asset_manager.asset_family.validation.attribute_as_main_media.not_found';
    public const INVALID_ATTRIBUTE_TYPE = 'pim_asset_manager.asset_family.validation.attribute_as_main_media.invalid_type';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'akeneo_assetmanager.validator.asset_family.attribute_as_main_media';
    }
}
