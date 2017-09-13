<?php

namespace Pim\Bundle\ApiBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\ConnectionCloser;
use Akeneo\Test\Integration\DatabaseSchemaHandler;
use Akeneo\Test\Integration\FixturesLoader;
use Pim\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Test case dedicated to PIM API interaction including authentication handling.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class ApiTestCase extends WebTestCase
{
    const USERNAME = 'admin';
    const PASSWORD = 'admin';

    /**
     * @return Configuration
     */
    abstract protected function getConfiguration();

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        static::bootKernel(['debug' => false]);

        $configuration = $this->getConfiguration();
        $databaseSchemaHandler = $this->getDatabaseSchemaHandler();

        $fixturesLoader = $this->getFixturesLoader($configuration, $databaseSchemaHandler);
        $fixturesLoader->load();

        $this->resetIndex();
    }

    /**
     * Adds a valid access token to the client, so it is included in all its requests.
     *
     * @param array  $options
     * @param array  $server
     * @param string $clientId
     * @param string $secret
     * @param string $username
     * @param string $password
     * @param string $accessToken
     * @param string $refreshToken
     *
     * @return Client
     */
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

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function getParameter($service)
    {
        return static::$kernel->getContainer()->getParameter($service);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $connectionCloser = $this->getConnectionCloser();
        $connectionCloser->closeConnections();

        parent::tearDown();
    }

    /**
     * @return DatabaseSchemaHandler
     */
    protected function getDatabaseSchemaHandler()
    {
        return new DatabaseSchemaHandler(static::$kernel);
    }

    /**
     * @param Configuration         $configuration
     * @param DatabaseSchemaHandler $databaseSchemaHandler
     *
     * @return FixturesLoader
     */
    protected function getFixturesLoader(Configuration $configuration, DatabaseSchemaHandler $databaseSchemaHandler)
    {
        return new FixturesLoader(static::$kernel, $configuration, $databaseSchemaHandler);
    }

    /**
     * @return ConnectionCloser
     */
    protected function getConnectionCloser()
    {
        return new ConnectionCloser(static::$kernel->getContainer());
    }

    /**
     * Look in every fixture directory if a fixture $name exists.
     * And return the pathname of the fixture if it exists.
     *
     * @param string $name
     *
     * @throws \Exception if no fixture $name has been found
     *
     * @return string
     */
    protected function getFixturePath($name)
    {
        $configuration = $this->getConfiguration();
        foreach ($configuration->getFixtureDirectories() as $fixtureDirectory) {
            $path = $fixtureDirectory . $name;
            if (is_file($path) && false !== realpath($path)) {
                return realpath($path);
            }
        }

        throw new \Exception(sprintf('The fixture "%s" does not exist.', $name));
    }

    /**
     * Resets the index used for the integration tests query
     */
    private function resetIndex()
    {
        $this->get('akeneo_elasticsearch.client.product')->resetIndex();
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->resetIndex();
    }

    /**
     * Execute a request where the response is streamed by chunk.
     *
     * The whole content of the request and the whole content of the response
     * are loaded in memory.
     * Therefore, do not use this function with an high input/output volumetry.
     *
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     * @param array  $files
     * @param array  $server
     * @param string $content
     * @param bool   $changeHistory
     *
     * @return array
     */
    protected function executeStreamRequest(
        $method,
        $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        $content = null,
        $changeHistory = true
    ) {
        $streamedContent = '';

        ob_start(function ($buffer) use (&$streamedContent) {
            $streamedContent .= $buffer;

            return '';
        });

        $client = $this->createAuthenticatedClient();
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);
        $client->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);

        ob_end_flush();

        $response = [
            'http_response' => $client->getResponse(),
            'content'       => $streamedContent,
        ];

        return $response;
    }
}
