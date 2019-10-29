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
    const USERNAME = 'admin';
    const PASSWORD = 'admin';

    use TestCaseTrait;

    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        static::$kernel->getContainer()
            ->get('akeneo_integration_tests.security.system_user_authenticator')
            ->createSystemUser();
    }

    protected function createAuthenticatedClient() {
        [$clientId, $secret] = $this->createOAuthClient();
        [$accessToken, $refreshToken] = $this->authenticate($clientId, $secret, self::USERNAME, self::PASSWORD);

        $client = static::createClient();
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $accessToken);
        $aclManager = $this->get('oro_security.acl.manager');
        $aclManager->clearCache();
        $client->setServerParameter('CONTENT_TYPE', 'application/json');

        return $client;
    }

    private function createOAuthClient(): array
    {
        $consoleApp = new Application(static::$kernel);
        $consoleApp->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'pim:oauth-server:create-client',
            'label'   => 'Api test case client ' . rand(),
        ]);
        $output = new BufferedOutput();
        $consoleApp->run($input, $output);
        $content = $output->fetch();
        preg_match('/client_id: (.+)\nsecret: (.+)\nlabel: (.+)$/', $content, $matches);

        return [$matches[1], $matches[2]];
    }

    private function authenticate(string $clientId, string $secret, string $username, string $password): array
    {
        $webClient = static::createClient();
        $webClient->request('POST', 'api/oauth/v1/token',
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
