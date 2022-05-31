<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\AddAclToRolesCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\AddAclToRolesHandler;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAclToRolesHandlerIntegration extends TestCase
{
    private AddAclToRolesHandler $handler;
    private AccessDecisionManagerInterface $accessDecisionManager;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->get(AddAclToRolesHandler::class);
        $this->accessDecisionManager = $this->get('security.access.decision_manager');
    }

    public function test_it_adds_acl_with_roles(): void
    {
        $user = $this->createAdminUser();
        $token = new UsernamePasswordToken($user->getUsername(), null, 'main', $user->getRoles());

        $isAllowed = $this->accessDecisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', 'akeneo_connectivity_connection_manage_apps'));
        $this->assertEquals(false, $isAllowed);

        $this->handler->handle(new AddAclToRolesCommand('akeneo_connectivity_connection_manage_apps', ['ROLE_ADMINISTRATOR']));

        $isAllowed = $this->accessDecisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', 'akeneo_connectivity_connection_manage_apps'));
        $this->assertEquals(true, $isAllowed);
    }
}
