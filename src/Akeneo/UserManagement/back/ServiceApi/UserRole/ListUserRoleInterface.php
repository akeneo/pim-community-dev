<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\ServiceApi\UserRole;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ListUserRoleInterface
{
    /**
     * @return UserRole[]
     */
    public function all(): array;
}
