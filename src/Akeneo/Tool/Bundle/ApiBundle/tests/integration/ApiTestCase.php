<?php

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\Test\IntegrationTestsBundle\Security\SystemUserAuthenticator;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

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

    /** @var KernelInterface */
    protected $testKernel;

    /** @var CatalogInterface */
    protected $catalog;

    /**
     * @return Configuration
     */
    abstract protected function getConfiguration();

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $authenticator = new SystemUserAuthenticator(static::$kernel->getContainer());
        $authenticator->createSystemUser();

        $this->testKernel = new \AppKernelTest('test', false);
        $this->testKernel->boot();

        $this->catalog = $this->testKernel->getContainer()->get('akeneo_integration_tests.configuration.catalog');
        $this->testKernel->getContainer()->set('akeneo_integration_tests.catalog.configuration', $this->getConfiguration());

        $fixturesLoader = $this->testKernel->getContainer()->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load();
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

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function getFromTestContainer(string $service)
    {
        return $this->testKernel->getContainer()->get($service);
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
    protected function tearDown(): void
    {
        $connectionCloser = $this->testKernel->getContainer()->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        parent::tearDown();
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
    protected function getFixturePath(string $name): string
    {
        $configuration = $this->getConfiguration();
        foreach ($configuration->getFixtureDirectories() as $fixtureDirectory) {
            $path = $fixtureDirectory . DIRECTORY_SEPARATOR . $name;
            if (is_file($path) && false !== realpath($path)) {
                return realpath($path);
            }
        }

        throw new \Exception(sprintf('The fixture "%s" does not exist.', $name));
    }

    protected function getFileInfoKey(string $path): string
    {
        if (!is_file($path)) {
            throw new \Exception(sprintf('The path "%s" does not exist.', $path));
        }

        $fileStorer = $this->get('akeneo_file_storage.file_storage.file.file_storer');
        $fileInfo = $fileStorer->store(new \SplFileInfo($path), FileStorage::CATALOG_STORAGE_ALIAS);

        return $fileInfo->getKey();
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
     * @param string $username
     * @param string $password
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
        $changeHistory = true,
        $username = self::USERNAME,
        $password = self::PASSWORD
    ) {
        $streamedContent = '';

        ob_start(function ($buffer) use (&$streamedContent) {
            $streamedContent .= $buffer;

            return '';
        });

        $client = $this->createAuthenticatedClient([], [], null, null, $username, $password);
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
