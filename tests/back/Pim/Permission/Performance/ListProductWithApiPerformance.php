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

    /**
     * We check SQL queries to avoid n+1 queries when filtering data.
     * We have to check the wall time also.
     *
     * Blackfire adds a non-negligeable overhead, but the target is to have 50 products/sec on the reference catalog.
     * As the overhead is constant, it's not a problem but we have to take it in account.
     */
    public function test_that_exporting_products_with_api_is_performant()
    {
        $clientConfiguration = $this->getBlackfireClientConfiguration();
        $clientConfiguration->setEnv('CI');

        $profileConfig = new Configuration();
        $profileConfig->setTitle('Export products with the API');

        $profileConfig->assert('metrics.sql.queries.count < 40', 'SQL queries');
        $profileConfig->assert('main.wall_time < 9s', 'Total time');
        $profileConfig->assert('main.peak_memory < 80mb', 'Memory');

        $client = $this->createAuthenticatedClient();

        $profile = $this->assertBlackfire($profileConfig, function () use ($client) {
            $client->request('GET', 'api/rest/v1/products?limit=100');
        });

        $response = $client->getResponse();
        Assert::assertSame(200, $response->getStatusCode());
        $products = json_decode($response->getContent(), true)['_embedded']['items'];
        Assert::assertSame(100, count($products));

        echo PHP_EOL. 'Profile complete: ' . $profile->getUrl() . PHP_EOL;
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
