<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Factory;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RoleWithPermissionsFactory implements SimpleFactoryInterface
{
    private SimpleFactoryInterface $roleFactory;

    public function __construct(SimpleFactoryInterface $roleFactory)
    {
        $this->roleFactory = $roleFactory;
    }

    public function create(): RoleWithPermissions
    {
        return RoleWithPermissions::createFromRoleAndPermissions(
            $this->roleFactory->create(),
            []
        );
    }
}
