<?php

namespace Akeneo\Test\UserManagement\Integration\Domain\Permissions;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Domain\Permissions\EditRolePermissionsRoleRepository;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class EditRolePermissionsRoleRepositoryIntegration extends TestCase
{
    private EditRolePermissionsRoleRepository $editRolePermissionsRoleRepository;
    private AccessDecisionManagerInterface $decisionManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->editRolePermissionsRoleRepository = $this->get(EditRolePermissionsRoleRepository::class);
        $this->decisionManager = $this->get('security.access.decision_manager');
    }


    public function testItGetRolesWithEditRolePermissions(): void
    {
        $editRoleRoles =$this->editRolePermissionsRoleRepository->getRolesWithMinimumEditRolePrivileges();
        foreach ($editRoleRoles as $editRole) {
            $this->assertRoleAclsAreGranted($editRole->getRole(), [
                'pim_user_role_edit' => true,
                'pim_user_role_index' => true,
                'oro_config_system' => true,
            ]);
        }
    }

    private function assertRoleAclsAreGranted(string $role, array $acls): void
    {
        $token = new UsernamePasswordToken('username', 'main', [$role]);

        foreach ($acls as $acl => $expectedValue) {
            assert(is_bool($expectedValue));

            $isAllowed = $this->decisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', $acl));
            $this->assertEquals($expectedValue, $isAllowed, sprintf('%s %s', $role, $acl));
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
