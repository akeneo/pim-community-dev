<?php

namespace Akeneo\UserManagement\Domain\Permissions;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
enum MinimumEditRolePermission: string
{
    case PIM_USER_ROLE_EDIT = 'action:pim_user_role_edit';
    case PIM_USER_ROLE_INDEX = 'action:pim_user_role_index';
    case ORO_CONFIG_SYSTEM = 'action:oro_config_system';

    public static function getAllValues(): array
    {
        return array_column(MinimumEditRolePermission::cases(), 'value');
    }
}
