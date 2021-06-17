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
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Allows to generate an http client authenticated with the given user with Oauth.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OauthAuthenticatedClientFactory
{
    private InMemoryUserRepository $userRepository;

    private KernelInterface $kernel;

    public function __construct(InMemoryUserRepository $userRepository, KernelInterface $kernel)
    {
        $this->userRepository = $userRepository;
        $this->kernel = $kernel;
    }

    public function logIn(string $username): Client
    {
        $user = $this->createUser($username);
        $client = $this->createClient();
        $token = new OAuthToken($user->getRoles());
        $client->getContainer()->get('security.token_storage')->setToken($token);

        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer fake_token');

        return $client;
    }

    private function createUser(string $username): User
    {
        $user = new User();
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
}
