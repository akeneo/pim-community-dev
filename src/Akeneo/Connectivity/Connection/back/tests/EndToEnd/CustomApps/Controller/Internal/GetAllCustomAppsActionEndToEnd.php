<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd\CustomApps\Controller\Internal;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\CustomAppLoader;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Controller\Internal\GetAllCustomAppsAction
 */
class GetAllCustomAppsActionEndToEnd extends WebTestCase
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

    public function test_it_gets_all_custom_app(): void
    {
        $adminUser = $this->authenticateAsAdmin();

        $this->customAppLoader->create('100eedac-ff5c-497b-899d-e2d64b6c59f9', $adminUser->getId());

        $this->client->request(
            'GET',
            '/rest/custom-apps',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals([
            'total' => 1,
            'apps' => [
                [
                    'id' => '100eedac-ff5c-497b-899d-e2d64b6c59f9',
                    'name' => 'custom_app_name',
                    'logo' => null,
                    'author' => 'John Doe',
                    'partner' => null,
                    'description' => null,
                    'url' => null,
                    'categories' => [],
                    'certified' => false,
                    'activate_url' => 'http://activate.test?pim_url=http%3A%2F%2Flocalhost%3A8080',
                    'callback_url' => 'http://callback.test',
                    'connected' => false,
                    'isPending' => false,
                    'isCustomApp' => true,
                ]
            ]
        ], $result);
    }
}
