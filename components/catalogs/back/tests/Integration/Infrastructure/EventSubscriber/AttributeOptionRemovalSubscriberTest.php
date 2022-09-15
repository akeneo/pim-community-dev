<?php

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\EventSubscriber;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use PHPUnit\Framework\Assert;

class AttributeOptionRemovalSubscriberTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDisablesCatalogsWhenAttributeOptionIsRemoved(): void
    {
        $client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);
        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'shopifi');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $colorAttribute = $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_simpleselect',
            'options' => [],
        ]);

        $redAttributeOption = $this->createAttributeOption('red', $colorAttribute, 0);
        $greenAttributeOption = $this->createAttributeOption('green', $colorAttribute, 1);
        $blueAttributeOption = $this->createAttributeOption('blue', $colorAttribute, 2);

        $this->createProduct('tshirt-blue', [
            new SetSimpleSelectValue('color', null, null, 'blue')
        ]);
        $this->createProduct('tshirt-green', [
            new SetSimpleSelectValue('color', null, null, 'green')
        ]);
        $this->setCatalogProductSelection('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['blue', 'green'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/product-identifiers',
            [
                'limit' => 2,
            ],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(['tshirt-blue', 'tshirt-green'], $payload['_embedded']['items']);

        $this->removeAttributeOption($blueAttributeOption);

        $client->request(
            'GET',
            '/api/rest/v1/catalogs/db1079b6-f397-4a6a-bae4-8658e64ad47c/product-identifiers',
            [
                'limit' => 2,
            ],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(200, $response->getStatusCode());
        Assert::assertEquals(['tshirt-blue', 'tshirt-green'], $payload['_embedded']['items']);
    }

    private function removeAttributeOption(AttributeOptionInterface $attributeOption): void
    {
        self::getContainer()->get('pim_catalog.remover.attribute_option')->remove($attributeOption);
    }
}
