<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\CustomApps\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\Internal\CreateCustomAppAction
 */
class CreateCustomAppActionEndToEnd extends WebTestCase
{
    private FilePersistedFeatureFlags $featureFlags;
    protected function setUp(): void
    {
        parent::setUp();

        $this->featureFlags = $this->get('feature_flags');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_creates_custom_app(): void
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
            '/rest/custom-apps',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json'
            ],
            \json_encode($data)
        );
        $response = $this->client->getResponse();
        $createdResult = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        Assert::assertArrayHasKey('clientId', $createdResult);
        Assert::assertArrayHasKey('clientSecret', $createdResult);
        Assert::assertIsString($createdResult['clientId']);
        Assert::assertIsString($createdResult['clientSecret']);
    }
}
