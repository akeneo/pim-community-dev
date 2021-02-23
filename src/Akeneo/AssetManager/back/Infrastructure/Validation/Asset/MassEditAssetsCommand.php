<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
class MassEditAssetsCommand extends Constraint
{
    public string $emptyValueCommandMessage = 'pim_asset_manager.asset.validation.mass_edit.updater_cannot_be_empty';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'akeneo_assetmanager.validator.asset.mass_edit_command_validator';
    }
}
