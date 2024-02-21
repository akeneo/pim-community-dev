<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Install;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Install\AddAclToRoles;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class AddAclToRolesIntegration extends TestCase
{
    private AddAclToRoles $addAclToRoles;
    private AccessDecisionManagerInterface $accessDecisionManager;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->addAclToRoles = $this->get(AddAclToRoles::class);
        $this->accessDecisionManager = $this->get('security.access.decision_manager');
    }

    public function test_it_adds_acl_to_roles(): void
    {
        $user = $this->createAdminUser();
        $token = new UsernamePasswordToken($user->getUserIdentifier(), 'main', $user->getRoles());

        $isAllowed = $this->accessDecisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', 'akeneo_connectivity_connection_manage_apps'));
        $this->assertFalse($isAllowed);

        $this->addAclToRoles->add('akeneo_connectivity_connection_manage_apps', ['ROLE_ADMINISTRATOR']);

        $isAllowed = $this->accessDecisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', 'akeneo_connectivity_connection_manage_apps'));
        $this->assertTrue($isAllowed);
    }
}
