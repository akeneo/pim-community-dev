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

class AttributeAssetTypeAssetFamilyShouldExist extends Constraint
{
    public const ERROR_MESSAGE = 'pim_asset_manager.attribute.validation.asset_type.asset_family_should_exist';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_assetmanager.validator.attribute.attribute_asset_type_asset_family_should_exist';
    }
}
