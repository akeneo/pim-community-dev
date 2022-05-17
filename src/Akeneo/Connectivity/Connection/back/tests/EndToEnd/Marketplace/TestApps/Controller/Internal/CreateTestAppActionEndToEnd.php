<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\Marketplace\TestApps\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateTestAppActionEndToEnd extends WebTestCase
{
    private FilePersistedFeatureFlags $featureFlags;

    public function test_it_creates_test_app(): void
    {
        $this->featureFlags->enable('app_developer_mode');
        $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_test_apps');

        $data = [
            'name' => 'Test app bynder',
            'callbackUrl' => 'http://any_url.test',
            'activateUrl' => 'http://activate.test',
        ];

        $this->client->request(
            'POST',
            'rest/marketplace/test-apps',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json'
            ],
            \json_encode($data)
        );
        $response = $this->client->getResponse();
        $createdResult = \json_decode($response->getContent(), true);

        Assert::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        Assert::assertArrayHasKey('clientId', $createdResult);
        Assert::assertArrayHasKey('clientSecret', $createdResult);
        Assert::assertIsString($createdResult['clientId']);
        Assert::assertIsString($createdResult['clientSecret']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlags = $this->get('feature_flags');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
