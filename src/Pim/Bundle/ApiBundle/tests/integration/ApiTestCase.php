<?php

namespace Pim\Bundle\ApiBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\ConnectionCloser;
use Akeneo\Test\Integration\DatabaseSchemaHandler;
use Akeneo\Test\Integration\FixturesLoader;
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

    /** @var int Count of executed tests inside the same test class */
    protected static $count = 0;

    /** @var string[] */
    protected static $accessTokens;

    /** @var string[] */
    protected static $refreshTokens;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        self::$count = 0;
        static::$accessTokens = [];
        static::$refreshTokens = [];
    }

    /**
     * @return Configuration
     */
    abstract protected function getConfiguration();

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        static::bootKernel();

        $configuration = $this->getConfiguration();

        self::$count++;

        if ($configuration->isDatabasePurgedForEachTest() || 1 === self::$count) {
            $this->resetIndex();
            $this->getDatabaseSchemaHandler()->reset();

            $fixturesLoader = $this->getFixturesLoader($configuration);
            $fixturesLoader->load();
        }
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
     *
     * @return Client
     */
    protected function createAuthenticatedClient(
        array $options = [],
        array $server = [],
        $clientId = null,
        $secret = null,
        $username = self::USERNAME,
        $password = self::PASSWORD
    ) {
        if (!isset(static::$accessTokens[$username]) || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            if (null === $clientId || null === $secret) {
                list($clientId, $secret) = $this->createOAuthClient();
            }

            $tokens = $this->authenticate($clientId, $secret, $username, $password);
            static::$accessTokens[$username] = $tokens[0];
            static::$refreshTokens[$username] = $tokens[1];
        }

        $client = static::createClient($options, $server);
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer '.static::$accessTokens[$username]);

        if (!isset($server['CONTENT_TYPE'])) {
            $client->setServerParameter('CONTENT_TYPE', 'application/json');
        }

        return $client;
    }

    /**
     * Creates a new OAuth client and returns its client id and secret.
     *
     * @return string[]
     */
    protected function createOAuthClient()
    {
        $consoleApp = new Application(static::$kernel);
        $consoleApp->setAutoExit(false);

        $input  = new ArrayInput(['command' => 'pim:oauth-server:create-client']);
        $output = new BufferedOutput();

        $consoleApp->run($input, $output);

        $content = $output->fetch();
        preg_match('/client_id: (.+)\nsecret: (.+)$/', $content, $matches);

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
     * @param Configuration $configuration
     *
     * @return FixturesLoader
     */
    protected function getFixturesLoader(Configuration $configuration)
    {
        return new FixturesLoader(static::$kernel, $configuration);
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
        $esClient = $this->get('akeneo_elasticsearch.client');
        $conf = $this->get('akeneo_elasticsearch.index_configuration.loader')->load();

        if ($esClient->hasIndex()) {
            $esClient->deleteIndex();
        }

        $esClient->createIndex($conf->buildAggregated());
    }
}
