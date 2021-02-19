<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector;

use Akeneo\UserManagement\Component\Model\RoleInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RoleWithPermissions
{
    private RoleInterface $role;
    private array $allowedPermissionIds;

    private function __construct(RoleInterface $role, array $allowedPermissionIds)
    {
        $this->role = $role;
        $this->allowedPermissionIds = $allowedPermissionIds;
    }

    public static function createFromRoleAndPermissionIds(
        RoleInterface $role,
        array $allowedPermissionIds
    ): RoleWithPermissions {
        Assert::allString($allowedPermissionIds);

        return new self($role, $allowedPermissionIds);
    }

    public function role(): RoleInterface
    {
        return $this->role;
    }

    public function allowedPermissionIds(): array
    {
        return $this->allowedPermissionIds;
    }
}
