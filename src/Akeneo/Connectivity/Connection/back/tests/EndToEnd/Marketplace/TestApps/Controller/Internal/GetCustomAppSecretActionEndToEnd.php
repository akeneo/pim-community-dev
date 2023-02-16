<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Marketplace\TestApps\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\TestApps\Persistence\GetTestAppSecretQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\TestAppLoader;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCustomAppSecretActionEndToEnd extends WebTestCase
{
    private FilePersistedFeatureFlags $featureFlags;
    private TestAppLoader $testAppLoader;
    private GetTestAppSecretQuery $getTestAppSecretQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlags = $this->get('feature_flags');
        $this->testAppLoader = $this->get(TestAppLoader::class);
        $this->getTestAppSecretQuery = $this->get(GetTestAppSecretQuery::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_gets_secret(): void
    {
        $this->featureFlags->enable('app_developer_mode');
        $user = $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_test_apps');

        $this->testAppLoader->create(
            clientId: '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            userId: $user->getId()
        );

        $secret = $this->getTestAppSecretQuery->execute('0dfce574-2238-4b13-b8cc-8d257ce7645b');

        $secretObfuscated = \str_pad(
            string: \substr($secret, -4),
            length: 34,
            pad_string: '*',
            pad_type: STR_PAD_LEFT
        );

        $this->client->request(
            'GET',
            '/rest/marketplace/custom-apps/0dfce574-2238-4b13-b8cc-8d257ce7645b/secret',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();
        $result = \json_decode($response->getContent(), true);

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEquals($secretObfuscated, $result);
    }

    public function test_it_gets_acl_error(): void
    {
        $this->client->request(
            'GET',
            '/rest/marketplace/custom-apps/0dfce574-2238-4b13-b8cc-8d257ce7645b/secret',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function test_it_gets_not_found_exception(): void
    {
        $this->featureFlags->enable('app_developer_mode');
        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_test_apps');

        $this->client->request(
            'GET',
            '/rest/marketplace/custom-apps/0dfce574-2238-4b13-b8cc-000000000/secret',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();

        Assert::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }
}
