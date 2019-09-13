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

use Akeneo\Test\Acceptance\User\InMemoryUserRepository;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * This class is capable of generating an http client authenticated with the given user.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthenticatedClientFactory
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var KernelInterface */
    private $kernel;

    public function __construct(InMemoryUserRepository $userRepository, KernelInterface $kernel)
    {
        $this->userRepository = $userRepository;
        $this->kernel = $kernel;
    }

    public function logIn(string $username): Client
    {
        $user = $this->createUser($username);
        $client = $this->createClient();
        $token = $this->getUserToken($user);
        $this->createSession($client, $token);

        return $client;
    }

    private function createUser(string $username): User
    {
        $user = new User();
        $user->addRole(new Role(User::ROLE_DEFAULT));
        $user->setUsername($username);
        $this->userRepository->save($user);

        return $user;
    }

    private function createClient(): Client
    {
        $client = new Client($this->kernel);
        $client->disableReboot();
        $client->followRedirects();

        return $client;
    }

    private function getUserToken(User $user): UsernamePasswordToken
    {
        return new UsernamePasswordToken($user, null, 'main', $user->getRoles());
    }

    private function createSession(Client $client, TokenInterface $token): void
    {
        $session = $client->getContainer()->get('session');
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }
}
