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

namespace Akeneo\EnrichedEntity\tests\back\Common\Helper;

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

    public function __construct(UserRepositoryInterface $userRepository, KernelInterface $kernel)
    {
        $this->userRepository = $userRepository;
        $this->kernel = $kernel;
    }

    public function logIn(string $username): Client
    {
        $client = $this->createClient();
        $token = $this->getUserToken($username);
        $this->createSession($client, $token);

        return $client;
    }

    private function createClient(): Client
    {
        $client = new Client($this->kernel);
        $client->disableReboot();
        $client->followRedirects();

        return $client;
    }

    private function getUserToken(string $username): UsernamePasswordToken
    {
        $user = $this->userRepository->findOneBy(['username' => $username]);
        if (null === $user) {
            throw new \LogicException(sprintf('User with username "%s" does not exist.', $username));
        }

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
