<?php

use Blackfire\Bridge\PhpUnit\TestCaseTrait;
use Blackfire\Client;
use Blackfire\ClientConfiguration;
use Blackfire\Profile\Configuration;
use PHPUnit\Framework\TestCase;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Akeneo\Test\IntegrationTestsBundle\Security\SystemUserAuthenticator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;


/**
 * @group blackfire
 */
class IntegrationTest extends WebTestCase
{
    const USERNAME = 'admin';
    const PASSWORD = 'admin';

    use TestCaseTrait;

    public function setUp(): void {
        static::bootKernel(['debug' => false]);
        $authenticator = new SystemUserAuthenticator(static::$kernel->getContainer());
        $authenticator->createSystemUser();
    }

    protected function createAuthenticatedClient(
        array $options = [],
        array $server = [],
        $clientId = null,
        $secret = null,
        $username = self::USERNAME,
        $password = self::PASSWORD,
        $accessToken = null,
        $refreshToken = null
    ) {
        if (null === $clientId || null === $secret) {
            list($clientId, $secret) = $this->createOAuthClient();
        }

        if (null === $accessToken || null === $refreshToken) {
            list($accessToken, $refreshToken) = $this->authenticate($clientId, $secret, $username, $password);
        }

        $client = static::createClient($options, $server);
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer '.$accessToken);

        $aclManager = $this->get('oro_security.acl.manager');
        $aclManager->clearCache();

        if (!isset($server['CONTENT_TYPE'])) {
            $client->setServerParameter('CONTENT_TYPE', 'application/json');
        }

        return $client;
    }

    /**
     * Creates a new OAuth client and returns its client id and secret.
     *
     * @param string|null $label
     *
     * @return string[]
     */
    protected function createOAuthClient(?string $label = null): array
    {
        $consoleApp = new Application(static::$kernel);
        $consoleApp->setAutoExit(false);

        $input  = new ArrayInput([
            'command' => 'pim:oauth-server:create-client',
            'label'   => null !== $label ? $label : 'Api test case client',
        ]);
        $output = new BufferedOutput();

        $consoleApp->run($input, $output);

        $content = $output->fetch();
        preg_match('/client_id: (.+)\nsecret: (.+)\nlabel: (.+)$/', $content, $matches);

        return [$matches[1], $matches[2]];
    }

      /**
     * Authenticates a user by calling the token route and returns the access token and the refresh token.
     *
     * @param string $clientId
     * @param string $secret
     * @param string $username
     * @param string $password
     *
     * @return string[]
     */
    protected function authenticate($clientId, $secret, $username, $password)
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

     /**
     * @param string $service
     *
     * @return mixed
     */
    protected function get($service)
    {
        return static::$kernel->getContainer()->get($service);
    }

    public function testSomething()
    {
        $config = new ClientConfiguration(getenv('BLACKFIRE_CLIENT_ID'), getenv('BLACKFIRE_CLIENT_TOKEN'));
        $blackfire = new Client($config);

        $profileConfig = new Configuration();
        $profileConfig->setTitle('Export products with the API');
        $profileConfig ->assert('metrics.sql.queries.count > 5', 'SQL queries') ;
        $client = $this->createAuthenticatedClient();

        $profile = $this->assertBlackfire($profileConfig, function () use ($client) {
           $response = $client->request('GET', 'api/rest/v1/products?limit=100');

        });

        echo 'Profile complete:'.$profile->getUrl();
        $tests = $profile->getTests();
        $isErrored = $profile->isErrored();
        var_dump($isErrored);
    }
}
