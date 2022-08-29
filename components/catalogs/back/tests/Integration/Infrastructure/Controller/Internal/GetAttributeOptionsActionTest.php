<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetAttributeOptionsAction
 */
class GetAttributeOptionsActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsAttributeOptions(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');
        $this->createAttribute([
            'code' => 'clothing_size',
            'type' => 'pim_catalog_simpleselect',
            'options' => ['XS', 'S', 'M', 'L', 'XL'],
        ]);

        $client->request(
            'GET',
            '/rest/catalogs/attributes/clothing_size/options',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $options = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertCount(5, $options);
        $this->assertArrayHasKey('code', $options[0]);
        $this->assertArrayHasKey('label', $options[0]);
    }
}
