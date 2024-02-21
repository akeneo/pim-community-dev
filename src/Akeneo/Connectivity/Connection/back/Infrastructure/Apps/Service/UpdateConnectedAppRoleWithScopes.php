<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Security\ScopeMapperRegistryInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Service\UpdateConnectedAppRoleWithScopesInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetConnectedAppRoleIdentifierQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateConnectedAppRoleWithScopes implements UpdateConnectedAppRoleWithScopesInterface
{
    public function __construct(
        private GetConnectedAppRoleIdentifierQueryInterface $getConnectedAppRoleIdentifierQuery,
        private RoleRepositoryInterface $roleRepository,
        private ScopeMapperRegistryInterface $scopeMapperRegistry,
        private BulkSaverInterface $roleWithPermissionsSaver,
    ) {
    }

    public function execute(string $appId, array $scopes): void
    {
        $appRole = $this->getAppRole($appId);

        $permissions = ['action:pim_api_overall_access' => true];

        $allAcls = $this->scopeMapperRegistry->getAcls($this->scopeMapperRegistry->getAllScopes());
        $acls = $this->scopeMapperRegistry->getAcls($scopes);
        foreach ($allAcls as $acl) {
            $permissions[\sprintf('action:%s', $acl)] = \in_array($acl, $acls);
        }

        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissions($appRole, $permissions);
        $this->roleWithPermissionsSaver->saveAll([$roleWithPermissions]);
    }

    private function getAppRole(string $appId): RoleInterface
    {
        $appRoleIdentifier = $this->getConnectedAppRoleIdentifierQuery->execute($appId);
        if (null === $appRoleIdentifier) {
            throw new \LogicException("Connected app $appId should have a role");
        }

        $appRole = $this->roleRepository->findOneByIdentifier($appRoleIdentifier);
        if (!$appRole instanceof RoleInterface) {
            throw new \LogicException("Role entity not found for $appRoleIdentifier");
        }

        return $appRole;
    }
}
