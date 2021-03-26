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
    private array $permissions;

    private function __construct(RoleInterface $role, array $privileges)
    {
        $this->role = $role;
        $this->setPermissions($privileges);
    }

    public static function createFromRoleAndPermissions(RoleInterface $role, array $permissions): RoleWithPermissions
    {
        return new self($role, $permissions);
    }

    public function getId(): ?int
    {
        return $this->role->getId();
    }

    public function role(): RoleInterface
    {
        return $this->role;
    }

    public function permissions(): array
    {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): void
    {
        Assert::allBoolean($permissions);
        Assert::allStringNotEmpty(\array_keys($permissions));
        $this->permissions = $permissions;
    }
}
