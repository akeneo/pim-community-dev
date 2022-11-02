<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeOptions extends Constraint
{
    public const MESSAGE_TOO_MANY_OPTIONS = 'pim_asset_manager.attribute.validation.options.too_many';
    public const MESSAGE_OPTION_DUPLICATED = 'pim_asset_manager.attribute.validation.options.duplicated';

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'akeneo_assetmanager.validator.asset.attribute_options';
    }
}
