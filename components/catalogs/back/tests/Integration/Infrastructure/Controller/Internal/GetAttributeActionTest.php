<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\GetAttributeAction
 */
class GetAttributeActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsAnAttribute(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);

        $client->request(
            'GET',
            '/rest/catalogs/attributes/name',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $attribute = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertSame('name', $attribute['code']);
        Assert::assertSame([
            'code',
            'label',
            'type',
            'scopable',
            'localizable',
            'attribute_group_code',
            'attribute_group_label',
        ], \array_keys($attribute));
    }
}
