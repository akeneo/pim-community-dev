<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service;

use Akeneo\UserManagement\Component\Factory\UserFactory;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateAppUserWithPermissions
{
    private OAuthScopeTransformer $authScopeTransformer;

    public function __construct(OAuthScopeTransformer $authScopeTransformer)
    {
        $this->authScopeTransformer = $authScopeTransformer;
    }
    public function handle(array $scopes): void
    {
        $aclPermissionIds = $this->authScopeTransformer->transform($scopes);

        /**
         * @todo: CREATE USER WITH PERMISSIONS FROM THE SCOPE
         * @see Akeneo\UserManagement\Component\Factory\UserFactory
         * @see Akeneo\UserManagement\Component\Factory\RoleWithPermissionsFactory
         * -> create a role
         * -> create a user
         * -> create a user group
         * associate this user to this role
         * associate this user to this user group
         */

        //dd($scopes, $aclPermissionIds);
    }
}
