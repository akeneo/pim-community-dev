<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetAttributesByTargetTypeAndTargetFormatAction
 */
class GetAttributesByTargetTypeAndTargetFormatActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsAttributesByTargetTypeAndTargetFormat(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');
        $this->createAttributeGroup(['code' => 'marketing']);
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'group' => 'marketing',
        ]);
        $this->createAttribute([
            'code' => 'release_date',
            'type' => 'pim_catalog_date',
        ]);

        $client->request(
            'GET',
            '/rest/catalogs/attributes_by_target_type_and_target_format',
            [
                'targetType' => 'string',
                'targetFormat' => null,
            ],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $attributes = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertCount(1, $attributes);
        Assert::assertArrayHasKey('code', $attributes[0]);
        Assert::assertArrayHasKey('label', $attributes[0]);
        Assert::assertArrayHasKey('type', $attributes[0]);
        Assert::assertArrayHasKey('scopable', $attributes[0]);
        Assert::assertArrayHasKey('localizable', $attributes[0]);
        Assert::assertArrayHasKey('attribute_group_code', $attributes[0]);
        Assert::assertArrayHasKey('attribute_group_label', $attributes[0]);
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
            '/rest/catalogs/attributes_by_target_type_and_target_format',
            [
                'search' => 'name',
                'targetType' => 'string',
            ],
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
        Assert::assertArrayHasKey('label', $attributes[0]);
        Assert::assertArrayHasKey('type', $attributes[0]);
        Assert::assertArrayHasKey('scopable', $attributes[0]);
        Assert::assertArrayHasKey('localizable', $attributes[0]);
        Assert::assertArrayHasKey('attribute_group_code', $attributes[0]);
        Assert::assertArrayHasKey('attribute_group_label', $attributes[0]);
    }

    public function testItThrowsABadRequestException(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);

        $client->request(
            'GET',
            '/rest/catalogs/attributes_by_target_type_and_target_format',
            ['targetType' => 'unexpected_type'],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(400, $response->getStatusCode());
    }
}
