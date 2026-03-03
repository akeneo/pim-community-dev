<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Service;

use Akeneo\Connectivity\Connection\Application\Apps\Security\ScopeMapperRegistryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetConnectedAppRoleIdentifierQueryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectedAppRoleWithScopesSpec extends ObjectBehavior
{
    public function let(
        GetConnectedAppRoleIdentifierQueryInterface $getConnectedAppRoleIdentifierQuery,
        RoleRepositoryInterface $roleRepository,
        ScopeMapperRegistryInterface $scopeMapperRegistry,
        BulkSaverInterface $roleWithPermissionsSaver,
        RoleInterface $role,
    ): void {
        $getConnectedAppRoleIdentifierQuery->execute('connected_app_id')->willReturn('ROLE_CONNECTED_APP');
        $roleRepository->findOneByIdentifier('ROLE_CONNECTED_APP')->willReturn($role->getWrappedObject());

        $scopeMapperRegistry->getAcls(['scopeA', 'scopeB', 'scopeC'])->willReturn([
            'some_acl_1',
            'some_acl_2',
            'some_acl_3',
        ]);
        $scopeMapperRegistry->getAcls(['scopeA', 'scopeB', 'scopeC', 'scopeD'])->willReturn([
            'some_acl_1',
            'some_acl_2',
            'some_acl_3',
            'some_acl_4',
        ]);

        $this->beConstructedWith(
            $getConnectedAppRoleIdentifierQuery,
            $roleRepository,
            $scopeMapperRegistry,
            $roleWithPermissionsSaver,
        );
    }

    public function it_updates_connected_app_role_with_new_acl_given_scopes(
        BulkSaverInterface $roleWithPermissionsSaver,
        RoleInterface $role,
        ScopeMapperRegistryInterface $scopeMapperRegistry,
    ): void {
        $scopeMapperRegistry->getAllScopes()->willReturn(['scopeA', 'scopeB', 'scopeC', 'scopeD']);
        $this->execute('connected_app_id', ['scopeA', 'scopeB', 'scopeC']);

        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissions($role->getWrappedObject(), [
            'action:pim_api_overall_access' => true,
            'action:some_acl_1' => true,
            'action:some_acl_2' => true,
            'action:some_acl_3' => true,
            'action:some_acl_4' => false,
        ]);
        $roleWithPermissionsSaver->saveAll([$roleWithPermissions])->shouldHaveBeenCalled();
    }

    public function it_throws_an_exception_when_no_role_identifier_is_found(
        GetConnectedAppRoleIdentifierQueryInterface $getConnectedAppRoleIdentifierQuery,
    ): void {
        $getConnectedAppRoleIdentifierQuery->execute('connected_app_id')->willReturn(null);

        $this
            ->shouldThrow(\LogicException::class)
            ->during('execute', ['connected_app_id', ['scopeA', 'scopeB', 'scopeC']]);
    }

    public function it_throws_an_exception_when_no_role_entity_is_found(
        RoleRepositoryInterface $roleRepository,
    ): void {
        $roleRepository->findOneByIdentifier('ROLE_CONNECTED_APP')->willReturn(null);

        $this
            ->shouldThrow(\LogicException::class)
            ->during('execute', ['connected_app_id', ['scopeA', 'scopeB', 'scopeC']]);
    }
}
