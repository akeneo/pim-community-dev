<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector;

use Akeneo\UserManagement\Component\Model\RoleInterface;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RoleWithPermissions
{
    private RoleInterface $role;
    private array $privileges;

    private function __construct(RoleInterface $role, array $privileges)
    {
        $this->role = $role;
        $this->privileges = $privileges;
    }

    public static function createFromRoleAndPrivileges(
        RoleInterface $role,
        array $privileges
    ): RoleWithPermissions {
        Assert::allIsInstanceOf($privileges, AclPrivilege::class);

        return new self($role, $privileges);
    }

    public function role(): RoleInterface
    {
        return $this->role;
    }

    /**
     * @return AclPrivilege[]
     */
    public function privileges(): array
    {
        return $this->privileges;
    }
}
