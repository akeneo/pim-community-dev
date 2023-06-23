<?php

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionHandler;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
        $this->catalog = $this->get('akeneo_integration_tests.catalogs');
        /** @var FilePersistedFeatureFlags $featureFlags*/
        $featureFlags = $this->get('feature_flags');
        $featureFlags->deleteFile();

        foreach ($this->getConfiguration()->getFeatureFlagsBeforeInstall() as $featureFlag) {
            $featureFlags->enable($featureFlag);
        }

        $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($this->getConfiguration());

        $authenticator = $this->get('akeneo_integration_tests.security.system_user_authenticator');
        $authenticator->createSystemUser();

        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    /**
     * Adds a valid access token to the client, so it is included in all its requests.
     */
    protected function createAuthenticatedClient(
        array $options = [],
        array $server = [],
        ?string $clientId = null,
        ?string $secret = null,
        ?string $username = self::USERNAME,
        ?string $password = self::PASSWORD,
        ?string $accessToken = null,
        ?string $refreshToken = null
    ): KernelBrowser {
        $options = array_merge($options, ['debug' => false]);

        if (null === $clientId || null === $secret) {
            [$clientId, $secret] = $this->createOAuthClient();
        }

        if (null === $accessToken || null === $refreshToken) {
            [$accessToken, $refreshToken] = $this->authenticate($clientId, $secret, $username, $password);
        }

        static::ensureKernelShutdown();
        $client = static::createClient($options, $server);
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer '.$accessToken);

        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

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
     * @deprecated
     *
     */
    protected function createOAuthClient(?string $label = null): array
    {
        $label = $label ?? uniqid('Api_Test_Case_client');
        $connection = $this->createConnection($label);

        return [$connection->clientId(), $connection->secret()];
    }

    /**
     * Creates an API Connection and returns it with its credentials
     */
    protected function createConnection(
        string $code = 'API_Test',
        ?string $label = 'API_Test',
        ?string $flowType = FlowType::OTHER,
        ?bool $auditable = true
    ): ConnectionWithCredentials {
        $createConnectionCommand = new CreateConnectionCommand($code, $label, $flowType, $auditable);

        $connection = $this->get(CreateConnectionHandler::class)->handle($createConnectionCommand);

        $user = $this->get('pim_user.manager')->loadUserByUsername($connection->username());

        $adminRole = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_ADMINISTRATOR');
        $user->addRole($adminRole);

        $userRole = $this->get('pim_user.repository.role')->findOneByIdentifier(User::ROLE_DEFAULT);
        $user->removeRole($userRole);

        $group = $this->get('pim_user.repository.group')->findOneByIdentifier('IT support');
        $user->addGroup($group);

        $this->get('validator')->validate($user);
        $this->get('pim_user.saver.user')->save($user);

        return $connection;
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
        static::ensureKernelShutdown();
        $webClient = static::createClient(['debug' => false]);
        $webClient->request(
            'POST',
            'api/oauth/v1/token',
            [
                'username' => $username,
                'password' => $password,
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW' => $secret,
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        $response = $webClient->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        return [
            $responseBody['access_token'],
            $responseBody['refresh_token'],
        ];
    }

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function get(string $service)
    {
        return static::getContainer()->get($service);
    }

    /**
     * @param string $parameter
     *
     * @return mixed
     */
    protected function getParameter(string $parameter)
    {
        return static::getContainer()->getParameter($parameter);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $connectionCloser = $this->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        parent::tearDown();
    }

    /**
     * Look in every fixture directory if a fixture $name exists.
     * And return the pathname of the fixture if it exists.
     *
     * @param string $name
     *
     * @return string
     * @throws \Exception if no fixture $name has been found
     *
     */
    protected function getFixturePath(string $name): string
    {
        $configuration = $this->getConfiguration();
        foreach ($configuration->getFixtureDirectories() as $fixtureDirectory) {
            $path = $fixtureDirectory.DIRECTORY_SEPARATOR.$name;
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
     * @param array $parameters
     * @param array $files
     * @param array $server
     * @param string $content
     * @param bool $changeHistory
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

        ob_start(
            function ($buffer) use (&$streamedContent) {
                $streamedContent .= $buffer;

                return '';
            }
        );

        $client = $this->createAuthenticatedClient([], [], null, null, $username, $password);
        $client->setServerParameter('CONTENT_TYPE', StreamResourceResponse::CONTENT_TYPE);
        $client->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);

        ob_end_flush();

        $response = [
            'http_response' => $client->getResponse(),
            'content' => $streamedContent,
        ];

        return $response;
    }

    protected function createAdminUser(): UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername(self::USERNAME);
        $user->setPlainPassword(self::PASSWORD);
        $user->setEmail('admin@example.com');
        $user->setSalt('E1F53135E559C253');
        $user->setFirstName('John');
        $user->setLastName('Doe');

        $this->get('pim_user.manager')->updatePassword($user);

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

        return $user;
    }

    /**
     * See https://github.com/symfony/symfony/commit/76f6c97416aca79e24a5b3e20e182fd6cc064b69...
     *
     * @param string $string
     *
     * @return string
     */
    protected function encodeStringWithSymfonyUrlGeneratorCompatibility(string $string): string
    {
        $toReplace = [
            // RFC 3986 explicitly allows those in the query/fragment to reference other URIs unencoded
            '%2F' => '/',
            '%3F' => '?',
            // reserved chars that have no special meaning for HTTP URIs in a query or fragment
            // this excludes esp. "&", "=" and also "+" because PHP would treat it as a space (form-encoded)
            '%40' => '@',
            '%3A' => ':',
            '%21' => '!',
            '%3B' => ';',
            '%2C' => ',',
            '%2A' => '*',
        ];

        return strtr(rawurlencode($string), $toReplace);
    }

    protected function removeAclFromRole(string $aclPrivilegeIdentityId, string $role = 'ROLE_ADMINISTRATOR'): void
    {
        $aclManager = $this->get('oro_security.acl.manager');
        $role = $this->get('pim_user.repository.role')->findOneByIdentifier($role);
        $privilege = new AclPrivilege();
        $identity = new AclPrivilegeIdentity($aclPrivilegeIdentityId);
        $privilege
            ->setIdentity($identity)
            ->addPermission(new AclPermission('EXECUTE', AccessLevel::NONE_LEVEL));
        $aclManager->getPrivilegeRepository()->savePrivileges(
            $aclManager->getSid($role),
            new ArrayCollection([$privilege])
        );
        $aclManager->flush();
        $aclManager->clearCache();
    }

    protected function getProductUuid(string $productIdentifier): ?UuidInterface
    {
        $productUuid = $this->get('database_connection')->executeQuery(<<<SQL
SELECT BIN_TO_UUID(product_uuid) AS uuid
FROM pim_catalog_product_unique_data
INNER JOIN pim_catalog_attribute ON pim_catalog_product_unique_data.attribute_id = pim_catalog_attribute.id
WHERE raw_data = :identifier
AND pim_catalog_attribute.main_identifier = 1
SQL,
            ['identifier' => $productIdentifier]
        )->fetchOne();

        return $productUuid ? Uuid::fromString($productUuid) : null;
    }
}
