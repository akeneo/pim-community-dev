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
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Public\UpdateCatalogAction
 * @covers \Akeneo\Catalogs\Application\Handler\UpdateCatalogHandler
 */
class UpdateCatalogActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItUpdatesTheCatalog(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);
        self::getContainer()->get(CommandBus::class)->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi',
        ));

        $client->request(
            'PATCH',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            \json_encode([
                'name' => 'new name',
            ]),
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertSame('new name', $payload['name']);
    }

    public function testItReturnsForbiddenWhenMissingPermissions(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([]);
        self::getContainer()->get(CommandBus::class)->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'shopifi',
        ));

        $client->request(
            'PATCH',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            \json_encode([
                'name' => 'new name',
            ]),
        );

        $response = $client->getResponse();

        Assert::assertEquals(403, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenCatalogDoesNotExist(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);

        $client->request(
            'PATCH',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            \json_encode([
                'name' => 'new name',
            ]),
        );

        $response = $client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }

    public function testItReturnsNotFoundWhenCatalogDoesNotBelongToCurrentUser(): void
    {
        $client = $this->getAuthenticatedPublicApiClient([
            'read_catalogs',
            'write_catalogs',
            'delete_catalogs',
        ]);
        $this->createUser('magendo');
        self::getContainer()->get(CommandBus::class)->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'magendo',
        ));

        $client->request(
            'PATCH',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
            \json_encode([
                'name' => 'new name',
            ]),
        );

        $response = $client->getResponse();

        Assert::assertEquals(404, $response->getStatusCode());
    }
}
