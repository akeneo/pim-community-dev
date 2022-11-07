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

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamilyPermission;

use Symfony\Component\Validator\Constraint;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class AtLeastOneUserGroupShouldHaveEditPermission extends Constraint
{
    public const ERROR_MESSAGE = 'pim_asset_manager.asset_family.validation.asset_family_permissions.at_least_one_user_group_should_have_edit_permission';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'akeneo_assetmanager.validator.asset_family_permission.at_least_one_user_group_should_have_edit_permission';
    }
}
