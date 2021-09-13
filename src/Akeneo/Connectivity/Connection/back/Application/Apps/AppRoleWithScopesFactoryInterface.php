<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps;

use Akeneo\UserManagement\Component\Model\RoleInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AppRoleWithScopesFactoryInterface
{
    /**
     * @param string[] $scopes
     */
    public function createRole(string $label, array $scopes): RoleInterface;
}
