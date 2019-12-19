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
class EditMediaFileValueCommand extends Constraint
{
    public const FILE_EXTENSION_NOT_ALLOWED_MESSAGE = 'pim_asset_manager.asset.validation.file.extension_not_allowed';
    public const FILE_SIZE_EXCEEDED_MESSAGE = 'pim_asset_manager.asset.validation.file.file_size_exceeded';
    public const FILE_SHOULD_EXIST = 'pim_asset_manager.asset.validation.file.should_exist';
    public const TARGET_READONLY = 'pim_asset_manager.asset.validation.file.target_readonly';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_assetmanager.validator.asset.edit_media_file_value_command';
    }
}
