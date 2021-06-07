<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Common\Helper;

use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * This class is capable of generating an http client authenticated with the given user.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthenticatedClient
{
    private UserRepositoryInterface $userRepository;

    private SessionInterface $session;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SessionInterface $session
    ) {
        $this->userRepository = $userRepository;
        $this->session = $session;
    }

    public function logIn(KernelBrowser $kernelBrowser, string $username): void
    {
        $user = $this->createUser($username);
        $token = $this->getUserToken($user);
        $this->createSession($kernelBrowser, $token);
    }

    private function createUser(string $username): User
    {
        $user = new User();
        $user->addRole(new Role(User::ROLE_DEFAULT));
        $user->setUsername($username);
        $this->userRepository->save($user);

        return $user;
    }

    private function getUserToken(User $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, null, 'main', $user->getRoles());
    }

    private function createSession(KernelBrowser $client, TokenInterface $token): void
    {
        $this->session->set('_security_main', serialize($token));
        $this->session->save();

        $cookie = new Cookie($this->session->getName(), $this->session->getId());
        $client->getCookieJar()->set($cookie);
    }
}
