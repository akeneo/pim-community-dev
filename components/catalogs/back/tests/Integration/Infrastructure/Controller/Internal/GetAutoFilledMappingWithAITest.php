<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Public\GetAutoFilledMappingWithAI
 */
class GetAutoFilledMappingWithAITest extends IntegrationTestCase
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
            '/rest/catalogs/attributes-by-target-type-and-target-format',
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
        // @todo rework to be more specific when there will be a combination type/format that will match only one attribute
        Assert::assertCount(2, $attributes);
        foreach ($attributes as $attribute) {
            Assert::assertArrayHasKey('code', $attribute);
            Assert::assertArrayHasKey('label', $attribute);
            Assert::assertArrayHasKey('type', $attribute);
            Assert::assertArrayHasKey('scopable', $attribute);
            Assert::assertArrayHasKey('localizable', $attribute);
            Assert::assertArrayHasKey('attribute_group_code', $attribute);
            Assert::assertArrayHasKey('attribute_group_label', $attribute);
        }

        Assert::assertEquals('name', $attributes[0]['code']);
        Assert::assertEquals('sku', $attributes[1]['code']);
    }

}
