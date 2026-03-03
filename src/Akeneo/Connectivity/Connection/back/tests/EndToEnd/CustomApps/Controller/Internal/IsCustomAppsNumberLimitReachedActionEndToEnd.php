<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\CustomApps\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\CustomAppLoader;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsCustomAppsNumberLimitReachedActionEndToEnd extends WebTestCase
{
    private ?CustomAppLoader $customAppLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customAppLoader = $this->get(CustomAppLoader::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_custom_app_not_limit_reached_flag(): void
    {
        $user = $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_test_apps');

        $this->customAppLoader->create('111eedac-ff5c-497b-899d-e2d64b6c59f9', $user->getId());
        $this->customAppLoader->create('222eedac-ff5c-497b-899d-e2d64b6c59f9', $user->getId());
        $this->customAppLoader->create('333eedac-ff5c-497b-899d-e2d64b6c59f9', $user->getId());

        $this->client->request(
            'GET',
            '/rest/custom-apps/max-limit-reached',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $response = $this->client->getResponse();
        $result = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        Assert::assertEquals(false, $result);
    }
}
