<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetAttributesAction
 */
class GetAttributesActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsAttributes(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);
        $this->createAttribute([
            'code' => 'description',
            'type' => 'pim_catalog_text',
        ]);

        $client->request(
            'GET',
            '/rest/catalogs/attributes',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $attributes = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        // 3 attributes because the catalog has a SKU attribute by default
        Assert::assertCount(3, $attributes);
        Assert::assertArrayHasKey('code', $attributes[0]);
        Assert::assertArrayHasKey('label', $attributes[0]);
        Assert::assertArrayHasKey('type', $attributes[0]);
        Assert::assertArrayHasKey('scopable', $attributes[0]);
        Assert::assertArrayHasKey('localizable', $attributes[0]);
    }

    public function testItSearchesAttributes(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);
        $this->createAttribute([
            'code' => 'description',
            'type' => 'pim_catalog_text',
        ]);

        $client->request(
            'GET',
            '/rest/catalogs/attributes',
            ['search' => 'name'],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $attributes = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertCount(1, $attributes);
        Assert::assertSame('name', $attributes[0]['code']);
    }

    public function testItGetsAttributesByTypes(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'variation_name',
            'type' => 'pim_catalog_text',
            'scopable' => false,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'clothing_size',
            'type' => 'pim_catalog_simpleselect',
            'scopable' => false,
            'localizable' => false,
        ]);
        $this->createAttribute([
            'code' => 'collection',
            'type' => 'pim_catalog_multiselect',
            'scopable' => false,
            'localizable' => false,
        ]);

        $client->request(
            'GET',
            '/rest/catalogs/attributes',
            ['types' => 'text,simpleselect'],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $attributes = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals([
            [
                'code' => 'name',
                'label' => '[name]',
                'type' => 'pim_catalog_text',
                'scopable' => false,
                'localizable' => false,
            ],
            [
                'code' => 'variation_name',
                'label' => '[variation_name]',
                'type' => 'pim_catalog_text',
                'scopable' => false,
                'localizable' => false,
            ],
            [
                'code' => 'clothing_size',
                'label' => '[clothing_size]',
                'type' => 'pim_catalog_simpleselect',
                'scopable' => false,
                'localizable' => false,
            ]
        ], $attributes);
    }
}
