<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\InternalApi;

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
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
abstract class WebTestCase extends TestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::getContainer()->get('test.client');
    }

    protected function authenticateAsAdmin(): UserInterface
    {
        $user = $this->createAdminUser();

        $this->authenticate($user);

        return $user;
    }

    private function authenticate(UserInterface $user)
    {
        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, $firewallName, $user->getRoles());
        $session = $this->getSession();
        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    private function getSession(): SessionInterface
    {
        return $this->client->getContainer()->get('session');
    }

    /*
     * TODO check if needed
     */
    protected function addAclToRole(string $roleCode, string $acl): void
    {
        $this->changeAclInRole($roleCode, $acl, true);
    }

    /*
     * TODO check if needed
     */
    protected function removeAclFromRole(string $roleCode, string $acl): void
    {
        $this->changeAclInRole($roleCode, $acl, false);
    }

    /*
     * TODO check if needed
     */
    private function changeAclInRole(string $roleCode, string $acl, bool $enabled): void
    {
        /** @var AclManager $aclManager */
        $aclManager = $this->get('oro_security.acl.manager');
        /** @var RoleWithPermissionsRepository $roleWithPermissionsRepository */
        $roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        /** @var RoleWithPermissionsSaver $roleWithPermissionsSaver */
        $roleWithPermissionsSaver = $this->get('pim_user.saver.role_with_permissions');

        $roleWithPermissions = $roleWithPermissionsRepository->findOneByIdentifier($roleCode);
        assert(null !== $roleWithPermissions);

        $permissions = $roleWithPermissions->permissions();
        $permissions[sprintf('action:%s', $acl)] = $enabled;
        $roleWithPermissions->setPermissions($permissions);

        $roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

        $aclManager->flush();
        $aclManager->clearCache();
    }
}
