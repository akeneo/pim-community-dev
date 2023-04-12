<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Public;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Public\GetCatalogsAction
 * @covers \Akeneo\Catalogs\Application\Handler\GetCatalogsByOwnerUsernameHandler
 */
class GetCatalogsActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsPaginatedCatalogsByOwnerUsnermae(): void
    {
        $client = $this->getAuthenticatedPublicApiClient(['read_catalogs']);
        self::getContainer()->get(CommandBus::class)->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi',
        ));
        self::getContainer()->get(CommandBus::class)->execute(new CreateCatalogCommand(
            'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            'Store FR',
            'shopifi',
        ));
        self::getContainer()->get(CommandBus::class)->execute(new CreateCatalogCommand(
            '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d',
            'Store UK',
            'shopifi',
        ));

        $client->request(
            'GET',
            '/api/rest/v1/catalogs',
            [
                'page' => 1,
                'limit' => 2,
            ],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $firstPageResponse = $client->getResponse();
        $firstPagePayload = \json_decode($firstPageResponse->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $firstPageResponse->getStatusCode());
        Assert::assertCount(2, $firstPagePayload['_embedded']['items']);

        Assert::assertSame('27c53e59-ee6a-4215-a8f1-2fccbb67ba0d', $firstPagePayload['_embedded']['items'][0]['id']);
        Assert::assertSame('Store UK', $firstPagePayload['_embedded']['items'][0]['name']);
        Assert::assertSame(false, $firstPagePayload['_embedded']['items'][0]['enabled']);

        Assert::assertSame('db1079b6-f397-4a6a-bae4-8658e64ad47c', $firstPagePayload['_embedded']['items'][1]['id']);
    }

    public function testItReturnsForbiddenWhenMissingPermissions(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([]);

        $client->request(
            'GET',
            '/api/rest/v1/catalogs',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();

        Assert::assertEquals(403, $response->getStatusCode());
    }

    public function testItGetsBadRequestWithWrongPagination(): void
    {
        $client = $this->getAuthenticatedPublicApiClient(['read_catalogs']);
        self::getContainer()->get(CommandBus::class)->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi',
        ));

        $client->request(
            'GET',
            '/api/rest/v1/catalogs',
            [
                'page' => -1,
                'limit' => -1,
            ],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();

        Assert::assertEquals(400, $response->getStatusCode());
    }
}
