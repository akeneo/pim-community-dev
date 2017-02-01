<?php

namespace Pim\Bundle\ApiBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\ConnectionCloser;
use Akeneo\Test\Integration\DatabasePurger;
use Akeneo\Test\Integration\FixturesLoader;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Test case dedicated to PIM API interaction including authentication handling.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class ApiTestCase extends WebTestCase
{
    /** @var int Count of executed tests inside the same test class */
    protected static $count = 0;

    /** @var ContainerInterface */
    protected $container;

    /** @var string */
    protected $accessToken;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        self::$count = 0;
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

        $this->container = static::$kernel->getContainer();
        $configuration = $this->getConfiguration();

        self::$count++;

        if ($configuration->isDatabasePurgedForEachTest() || 1 === self::$count) {
            $databasePurger = $this->getDatabasePurger();
            $databasePurger->purge();

            $fixturesLoader = $this->getFixturesLoader($configuration);
            $fixturesLoader->load();
        }
    }

    /**
     * Adds a valid access token to the client, so it is included in all its requests.
     *
     * @param array $options
     * @param array $server
     *
     * @throws \Exception
     *
     * @return Client
     */
    protected function createAuthentifiedClient(array $options = [], array $server = [])
    {
        if (null === $this->accessToken) {
            $consoleApp = new Application(self::$kernel);
            $consoleApp->setAutoExit(false);

            $input  = new ArrayInput(['command' => 'pim:oauth-server:create-client']);
            $output = new BufferedOutput();

            $consoleApp->run($input, $output);

            $content = $output->fetch();
            preg_match('/client_id: (.+)\nsecret: (.+)$/', $content, $matches);
            $clientId = $matches[1];
            $secret   = $matches[2];

            $oauthClient = self::createClient();
            $oauthClient->request('POST', 'api/oauth/v1/token',
                [
                    'username'   => 'admin',
                    'password'   => 'admin',
                    'grant_type' => 'password',
                ],
                [],
                [
                    'PHP_AUTH_USER' => $clientId,
                    'PHP_AUTH_PW'   => $secret,
                    'CONTENT_TYPE'  => 'application/json',
                ]
            );

            $response = $oauthClient->getResponse();
            $responseBody = json_decode($response->getContent(), true);
            $this->accessToken = $responseBody['access_token'];
        }

        $client = self::createClient($options, $server);
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer '.$this->accessToken);
        $client->setServerParameter('CONTENT_TYPE', 'application/json');

        return $client;
    }

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function get($service)
    {
        return $this->container->get($service);
    }

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function getParameter($service)
    {
        return $this->container->getParameter($service);
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
     * @return DatabasePurger
     */
    protected function getDatabasePurger()
    {
        return new DatabasePurger($this->container);
    }

    /**
     * @param Configuration $configuration
     *
     * @return FixturesLoader
     */
    protected function getFixturesLoader(Configuration $configuration)
    {
        return new FixturesLoader($this->container, $configuration);
    }

    /**
     * @return ConnectionCloser
     */
    protected function getConnectionCloser()
    {
        return new ConnectionCloser($this->container);
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
}
