<?php
declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

final class DuplicateUserIntegration extends ControllerIntegrationTestCase
{
    private UserRepositoryInterface $userRepository;
    private LocaleRepositoryInterface $localeRepository;
    private RoleRepositoryInterface $roleRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->get('pim_user.repository.user');
        $this->roleRepository = $this->get('pim_user.repository.role');
        $this->localeRepository = $this->get('pim_catalog.repository.locale');
    }

    public function test_it_duplicates_a_user(): void
    {
        $user = $this->createUserWithGroupsAndRoles('test1', ['Redactor'], ['ROLE_USER']);
        $params = [
            'username' => 'test2',
            'password' => 'new_password',
            'password_repeat' => 'new_password',
            'first_name' => 'first',
            'last_name' => 'last',
            'email' => 'new@example.com',
        ];

        $this->logIn('admin');
        $response = $this->callRoute(
            'pim_user_user_rest_duplicate',
            ['identifier' => $user->getId()],
            Request::METHOD_POST,
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            [],
            \json_encode($params)
        );
        $this->assertStatusCode($response, Response::HTTP_OK);

        /** @var UserInterface $duplicatedUser */
        $duplicatedUser = $this->userRepository->findOneByIdentifier('test2');
        self::assertNotNull($duplicatedUser);
        self::assertNotSame($user->getId(), $duplicatedUser->getId());
        self::assertSame('test2', $duplicatedUser->getUsername());
        self::assertSame('first', $duplicatedUser->getFirstName());
        self::assertSame('last', $duplicatedUser->getLastName());
        self::assertSame('new@example.com', $duplicatedUser->getEmail());
        self::assertSame(0, $duplicatedUser->getLoginCount());
        self::assertTrue($duplicatedUser->hasGroup('Redactor'));
        self::assertFalse($duplicatedUser->hasGroup('Manager'));
        self::assertTrue($duplicatedUser->hasRole('ROLE_USER'));
        self::assertFalse($duplicatedUser->hasRole('ROLE_ADMINISTRATOR'));
        self::assertSame('de_DE', $duplicatedUser->getUiLocale()->getCode());
        self::assertSame('en_US', $duplicatedUser->getCatalogLocale()->getCode());
    }

    public function test_it_is_forbidden_when_logged_user_has_not_the_permission(): void
    {
        $user = $this->createUserWithGroupsAndRoles('test1', ['Redactor'], ['ROLE_USER']);
        $this->revokePermissionForRole('ROLE_USER', 'pim_user_user_create');
        $params = [
            'username' => 'test3',
            'password' => 'new_password',
            'password_repeat' => 'new_password',
            'first_name' => 'first',
            'last_name' => 'last',
            'email' => 'new@example.com',
        ];

        $this->logIn('test1');
        $response = $this->callRoute(
            'pim_user_user_rest_duplicate',
            ['identifier' => $user->getId()],
            Request::METHOD_POST,
            ['HTTP_X-Requested-With' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'],
            [],
            \json_encode($params)
        );
        $this->assertStatusCode($response, Response::HTTP_FORBIDDEN);
    }

    private function createUserWithGroupsAndRoles(string $username, array $groupNames, array $stringRoles): UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setId(uniqid());
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@example.com', uniqid()));
        $user->setPassword('fake');
        $user->setUILocale($this->localeRepository->findOneByIdentifier('de_DE'));
        $user->setCatalogLocale($this->localeRepository->findOneByIdentifier('en_US'));

        $groups = $this->get('pim_user.repository.group')->findAll();
        foreach ($groups as $group) {
            if (in_array($group->getName(), $groupNames)) {
                $user->addGroup($group);
            }
        }

        $roles = $this->get('pim_user.repository.role')->findAll();
        foreach ($roles as $role) {
            if (in_array($role->getRole(), $stringRoles)) {
                $user->addRole($role);
            }
        }

        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }

    private function revokePermissionForRole(string $stringRole, string $permission): void
    {
        $role = $this->roleRepository->findOneByIdentifier($stringRole);
        self::assertNotNull($role);

        $aclManager = $this->get('oro_security.acl.manager');
        $sid = $aclManager->getSid($role);

        foreach ($aclManager->getAllExtensions() as $extension) {
            foreach ($extension->getClasses() as $aclClassInfo) {
                if ($aclClassInfo->getClassName() === $permission) {
                    $oid = new ObjectIdentity($extension->getExtensionKey(), $aclClassInfo->getClassName());
                    $aclManager->setPermission($sid, $oid, AccessLevel::NONE_LEVEL, true);
                }
            }
        }

        $aclManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
