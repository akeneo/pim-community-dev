<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Performance;

use Blackfire\Bridge\PhpUnit\TestCaseTrait;
use Blackfire\Profile\Configuration;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Akeneo\Test\IntegrationTestsBundle\Security\SystemUserAuthenticator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use PHPUnit\Framework\Assert;

class ListProductWithApiPerformance extends WebTestCase
{
    const USERNAME = 'admin';
    const PASSWORD = 'admin';

    use TestCaseTrait;

    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $authenticator = new SystemUserAuthenticator(static::$kernel->getContainer());
        $authenticator->createSystemUser();
    }

    public function test_that_listing_the_products_with_api_is_performant()
    {
        $clientConfiguration = $this->getBlackfireClientConfiguration();
        $clientConfiguration->setEnv('CI');

        $profileConfig = new Configuration();
        $profileConfig->setTitle('Export products with the API');

        $profileConfig->assert('metrics.sql.queries.count < 1000', 'SQL queries');
        $profileConfig->assert('main.wall_time < 15s', 'Total time');
        $profileConfig->assert('main.peak_memory < 100mb', 'Memory');

        $client = $this->createAuthenticatedClient();

        $profile = $this->assertBlackfire($profileConfig, function () use ($client) {
            $client->request('GET', 'api/rest/v1/products?limit=100');
        });

        $response = $client->getResponse();
        Assert::assertSame(200, $response->getStatusCode());
        $products = json_decode($response->getContent(), true)['_embedded']['items'];
        Assert::assertSame(100, count($products));

        echo 'Profile complete: ' . $profile->getUrl();
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

    protected function authenticate(string $clientId, string $secret, string $username, string $password): array
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
