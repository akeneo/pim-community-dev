<?php

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\EventSubscriber;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\EventSubscriber\AttributeOptionRemovalSubscriber
 */
class AttributeOptionRemovalSubscriberTest extends IntegrationTestCase
{
    private ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDisablesCatalogsWhenAttributeOptionIsRemoved(): void
    {
        $this->getAuthenticatedInternalApiClient('admin');
        $this->createAttribute([
            'code' => 'color',
            'type' => 'pim_catalog_simpleselect',
            'options' => ['red', 'green', 'blue'],
        ]);

        $idUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $idFR = 'ed30425c-d9cf-468b-8bc7-fa346f41dd07';

        $this->createCatalog($idUS, 'Store US', 'shopifi');
        $this->createCatalog($idFR, 'Store FR', 'shopifi');

        $this->enableCatalog($idUS);
        $this->enableCatalog($idFR);

        $this->createProduct('tshirt-blue', [
            new SetSimpleSelectValue('color', null, null, 'blue')
        ]);
        $this->createProduct('tshirt-green', [
            new SetSimpleSelectValue('color', null, null, 'green')
        ]);

        $this->setCatalogProductSelection($idUS, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['blue', 'green'],
                'scope' => null,
                'locale' => null,
            ],
        ]);
        $this->setCatalogProductSelection($idFR, [
            [
                'field' => 'color',
                'operator' => Operator::IN_LIST,
                'value' => ['blue', 'red'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $this->client = $this->getAuthenticatedPublicApiClient(['read_catalogs', 'read_products']);

        $this->assertResponseEquals($idUS, 200, ['tshirt-blue', 'tshirt-green']);
        $this->assertResponseEquals($idFR, 200, ['tshirt-blue']);

        $this->removeAttributeOption('color.blue');

        $this->assertResponseEquals($idUS, 200, []);
        $this->assertResponseEquals($idFR, 200, []);
    }

    private function assertResponseEquals(string $catalogId, int $statusCode, array $expectedPayload): void
    {
        $this->client->request(
            'GET',
            \sprintf('/api/rest/v1/catalogs/%s/product-identifiers', $catalogId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        $response = $this->client->getResponse();
        $payload = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals($statusCode, $response->getStatusCode());
        Assert::assertEquals($expectedPayload, $payload['_embedded']['items']);
    }

    private function removeAttributeOption(string $code): void
    {
        $attributeOption = self::getContainer()->get('pim_catalog.repository.attribute_option')->findOneByIdentifier($code);
        self::getContainer()->get('pim_catalog.remover.attribute_option')->remove($attributeOption);
    }
}
