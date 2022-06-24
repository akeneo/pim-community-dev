<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCatalogCriteriaActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsCatalogCriteria(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();
        $this->createCatalog('ed30425c-d9cf-468b-8bc7-fa346f41dd07', 'Store FR', 'admin');

        $client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07/criteria',
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
            [
                'field' => 'enabled',
                'operator' => '=',
                'value' => true,
            ],
        ], $payload);
    }

    public function testItGetsNotFoundResponseWithWrongId(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();

        $client->request(
            'GET',
            '/rest/catalogs/ed30425c-d9cf-468b-8bc7-fa346f41dd07/criteria',
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
