<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Performance;

use Akeneo\UserManagement\Component\Model\User;
use Blackfire\Bridge\PhpUnit\TestCaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
abstract class AbstractApiPerformance extends WebTestCase
{
    use TestCaseTrait;

    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        static::$kernel->getContainer()
            ->get('akeneo_integration_tests.security.system_user_authenticator')
            ->createSystemUser();
    }

    protected function createAuthenticatedClient(string $connectionFlowType = null)
    {
        [$clientId, $secret, $username, $password] = $this->createApiConnection($connectionFlowType);
        $this->promoteUserToAdmin($username);

        [$accessToken] = $this->authenticate($clientId, $secret, $username, $password);

        $client = static::createClient();
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $accessToken);
        $aclManager = $this->get('oro_security.acl.manager');
        $aclManager->clearCache();
        $client->setServerParameter('CONTENT_TYPE', 'application/json');

        return $client;
    }

    private function createApiConnection(string $flowType = null): array
    {
        $consoleApp = new Application(static::$kernel);
        $consoleApp->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'akeneo:connectivity-connection:create',
            'code' => 'testcase_' . rand(),
            '--flow-type' => $flowType,
        ]);
        $output = new BufferedOutput();
        $consoleApp->run($input, $output);

        $content = $output->fetch();
        preg_match('/Client ID: (.+)\nSecret: (.+)\nUsername: (.+)\nPassword: (.+)\n/', $content, $matches);

        return [$matches[1], $matches[2], $matches[3], $matches[4]];
    }

    private function promoteUserToAdmin(string $username)
    {
        $user = $this->get('pim_user.manager')->loadUserByUsername($username);

        $adminRole = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_ADMINISTRATOR');
        if (null !== $adminRole) {
            $user->addRole($adminRole);
        }

        $userRole = $this->get('pim_user.repository.role')->findOneByIdentifier(User::ROLE_DEFAULT);
        if (null !== $userRole) {
            $user->removeRole($userRole);
        }

        $group = $this->get('pim_user.repository.group')->findOneByIdentifier('IT support');
        if (null !== $group) {
            $user->addGroup($group);
        }

        $this->get('validator')->validate($user);
        $this->get('pim_user.saver.user')->save($user);
    }

    private function authenticate(string $clientId, string $secret, string $username, string $password): array
    {
        $webClient = static::createClient();
        $webClient->request(
            'POST',
            'api/oauth/v1/token',
            [
                'username'   => $username,
                'password'   => $password,
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => $secret,
                'CONTENT_TYPE'  => 'application/json',
            ]
        );
        $response = $webClient->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        return [
            $responseBody['access_token'],
            $responseBody['refresh_token']
        ];
    }

    protected function get(string $service)
    {
        return static::$kernel->getContainer()->get($service);
    }
}
