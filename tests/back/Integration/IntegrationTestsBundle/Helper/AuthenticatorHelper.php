<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Helper;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Factory\UserFactory;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AuthenticatorHelper
{
    private UserRepositoryInterface $userRepository;
    private UserFactory $userFactory;
    private SaverInterface $userSaver;
    private GroupRepositoryInterface $groupRepository;
    private RoleRepositoryInterface $roleRepository;
    private TokenStorageInterface $tokenStorage;
    private SessionInterface $session;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserFactory $userFactory,
        SaverInterface $userSaver,
        GroupRepositoryInterface $groupRepository,
        RoleRepositoryInterface $roleRepository,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ) {
        $this->userRepository = $userRepository;
        $this->userFactory = $userFactory;
        $this->userSaver = $userSaver;
        $this->groupRepository = $groupRepository;
        $this->roleRepository = $roleRepository;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
    }

    public function logIn(string $username, ?KernelBrowser $client = null): void
    {
        $user = $this->userRepository->findOneByIdentifier($username);
        if (null === $user) {
            $user = $this->createUser($username);
        }

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        $this->session->set('_security_main', serialize($token));
        $this->session->save();

        if (null !== $client) {
            $cookie = new Cookie($this->session->getName(), $this->session->getId());
            $client->getCookieJar()->set($cookie);
        }
    }

    /**
     * Create a token with a user with all access.
     */
    private function createUser(string $username): User
    {
        $user = $this->userFactory->create();
        $user->setId(uniqid());
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@example.com', uniqid()));
        $user->setPassword('fake');
        $groups = $this->groupRepository->findAll();

        foreach ($groups as $group) {
            $user->addGroup($group);
        }

        $roles = $this->roleRepository->findAll();
        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $this->userSaver->save($user);

        return $user;
    }
}
