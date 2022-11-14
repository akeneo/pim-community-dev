<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

class GetProductMappingAttributesActionTest extends IntegrationTestCase
{
    private ?CommandBus $commandBus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = self::getContainer()->get(CommandBus::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsTheCatalogProductMappingSchema(): void
    {
        $client = $this->getAuthenticatedInternalApiClient();

        $this->createAttribute([
            'code' => 'title',
            'type' => 'pim_catalog_text',
            'labels' => [
                'en_US' => 'Title',
                'fr_FR' => 'Titre',
            ],
        ]);
        $this->createAttribute([
            'code' => 'description',
            'type' => 'pim_catalog_text',
            'labels' => [
                'fr_FR' => 'Description',
                'en_US' => 'Description',
            ],
        ]);
        $this->commandBus->execute(new CreateCatalogCommand(
            'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'Store US',
            'admin',
        ));
        $productMapping = [
            'uuid' => [
                'source' => 'uuid',
                'scope' => null,
                'locale' => null,
            ],
            'name' => [
                'source' => 'title',
                'scope' => 'ecommerce',
                'locale' => 'en_US',
            ],
            'description' => [
                'source' => 'description',
                'scope' => 'ecommerce',
                'locale' => 'en_US',
            ],
            'color' => [
                'source' => null,
                'scope' => null,
                'locale' => null,
            ],
        ];
        $this->setCatalogProductMapping('db1079b6-f397-4a6a-bae4-8658e64ad47c', $productMapping);

        $client->request(
            'GET',
            '/rest/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/mapping/product/attributes',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        $payload = $response->getContent();

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertSame([
            'description' => [
                'code' => 'description',
                'label' => 'Description',
            ],
            'title' => [
                'code' => 'title',
                'label' => 'Title',
            ],
        ], \json_decode($payload, true, 512, JSON_THROW_ON_ERROR));
    }
}
