<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCatalogDataActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsCatalogData(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();
        $this->createCatalog('ed30425c-d9cf-468b-8bc7-fa346f41dd07', 'Store FR', 'admin');

        $client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07/data',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true);

        Assert::assertEquals(200, $response->getStatusCode());

        Assert::assertEquals([
            'product_selection_criteria' => [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
            ],
        ], $payload);
    }

    public function testItGetsNotFoundResponseWithWrongId(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();
        $this->createCatalog('ed30425c-d9cf-468b-8bc7-fa346f41dd07', 'Store FR', 'admin');

        $client->request(
            'GET',
            '/rest/catalogs/wr0ngc-d9cf-468b-8bc7-fa346f41dd07/data',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }
}
