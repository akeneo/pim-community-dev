<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class WebTestCase extends TestCase
{
    /** @var KernelBrowser */
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::getContainer()->get('test.client');
    }

    protected function createConnection(string $code, string $label, string $flowType, bool $auditable): ConnectionWithCredentials
    {
        $createConnectionCommand = new CreateConnectionCommand($code, $label, $flowType, $auditable);

        return $this
            ->get(CreateConnectionHandler::class)
            ->handle($createConnectionCommand);
    }

    protected function authenticateAsAdmin(): UserInterface
    {
        $user = $this->createAdminUser();

        $this->authenticate($user);

        return $user;
    }

    private function authenticate(UserInterface $user): void
    {
        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, $firewallName, $user->getRoles());
        $session = $this->getSession();
        $session->set('_security_' . $firewallContext, \serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    private function getSession(): SessionInterface
    {
        return $this->client->getContainer()->get('session');
    }

    protected function addAclToRole(string $roleCode, string $acl): void
    {
        $this->changeAclInRole($roleCode, $acl, true);
    }

    protected function removeAclFromRole(string $roleCode, string $acl): void
    {
        $this->changeAclInRole($roleCode, $acl, false);
    }

    private function changeAclInRole(string $roleCode, string $acl, bool $enabled): void
    {
        /** @var AclManager $aclManager */
        $aclManager = $this->get('oro_security.acl.manager');
        /** @var RoleWithPermissionsRepository $roleWithPermissionsRepository */
        $roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        /** @var RoleWithPermissionsSaver $roleWithPermissionsSaver */
        $roleWithPermissionsSaver = $this->get('pim_user.saver.role_with_permissions');

        $roleWithPermissions = $roleWithPermissionsRepository->findOneByIdentifier($roleCode);
        \assert(null !== $roleWithPermissions);

        $permissions = $roleWithPermissions->permissions();
        $permissions[\sprintf('action:%s', $acl)] = $enabled;
        $roleWithPermissions->setPermissions($permissions);

        $roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

        $aclManager->flush();
        $aclManager->clearCache();
    }
}
