<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class EditMediaLinkValueCommand extends Constraint
{
    public const PROTOCOL_NOT_ALLOWED = 'pim_asset_manager.asset.validation.media_link.protocol_not_allowed';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_assetmanager.validator.asset.edit_media_link_value_command';
    }
}
