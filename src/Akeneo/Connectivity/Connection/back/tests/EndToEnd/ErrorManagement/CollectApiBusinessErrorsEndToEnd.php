<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CollectApiBusinessErrorsEndToEnd extends ApiTestCase
{
    /** @var Client */
    private $esClient;

    /*
    public function test_it_collects_an_unprocessable_entity(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);
        $this->createFamily([
            'code' => 'planeswalker',
            'attributes' => ['sku', 'name']
        ]);
        $this->createProduct('teferi_time_raveler', [
            'family' => 'planeswalker',
            'values' => [
                'name' => [['data' => 'Teferi, time raveler', 'locale' => null, 'scope' => null]]
            ]
        ]);

        $connection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);

        $client = $this->createAuthenticatedClient(
            [],
            [],
            $connection->clientId(),
            $connection->secret(),
            $connection->username(),
            $connection->password()
        );

        $content = <<<JSON
{
    "identifier": "teferi_time_raveler",
    "values": {
        "description": [{
            "locale": null,
            "scope": null,
            "data": "Each opponent can only cast spells any time they could cast a sorcery."
        }]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products', [], [], [], $content);
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        $expectedContent = json_decode($client->getResponse()->getContent(), true);

        $this->esClient->refreshIndex();
        $result = $this->esClient->search(["query" => ["term" => ["connection_code" => "erp"]]]);

        Assert::assertCount(1, $result['hits']['hits']);

        $doc = $result['hits']['hits'][0]['_source'];
        Assert::assertEquals('erp', $doc['connection_code']);
        Assert::assertEquals($expectedContent, $doc['content']);
    }

    public function test_it_collects_an_unprocessable_entity_with_deeper_errors()
    {
        $this->createAttribute([
            'code' => 'length',
            'type' => 'pim_catalog_metric',
            'metric_family' => 'Length',
            'default_metric_unit' => 'CENTIMETER',
            'negative_allowed' => false,
            'decimals_allowed' => false,
        ]);
        $this->createFamily([
            'code' => 'screen',
            'attributes' => ['sku', 'length']
        ]);
        $this->createProduct('big_screen', [
            'family' => 'screen',
            'values' => [
                'length' => [['data' => ['amount' => 5, 'unit' => 'meter'], 'locale' => null, 'scope' => null]]
            ]
        ]);

        $connection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $client = $this->createAuthenticatedClient(
            [],
            [],
            $connection->clientId(),
            $connection->secret(),
            $connection->username(),
            $connection->password()
        );

        $content = <<<JSON
{
    "identifier": "big_screen",
    "values": {
        "length": [{
            "locale": null,
            "scope": null,
            "data": {
                "amount": 2,
                "unit": "atchoum"
            }
        }]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/products', [], [], [], $content);
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        $expectedContent = json_decode($client->getResponse()->getContent(), true);

        $this->esClient->refreshIndex();
        $result = $this->esClient->search(["query" => ["term" => ["connection_code" => "erp"]]]);

        Assert::assertCount(2, $result['hits']['hits']);

        $doc1 = $result['hits']['hits'][0]['_source'];
        Assert::assertEquals('erp', $doc1['connection_code']);
        Assert::assertEquals($expectedContent['errors'][0], $doc1['content']);

        $doc2 = $result['hits']['hits'][1]['_source'];
        Assert::assertEquals('erp', $doc2['connection_code']);
        Assert::assertEquals($expectedContent['errors'][1], $doc2['content']);
    }
    */

    public function test_it_collects_errors_from_a_partial_update_list(): void
    {
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
        ]);
        $this->createFamily([
            'code' => 'shoes',
            'attributes' => ['sku', 'name']
        ]);
        $this->createProduct('high-top_sneakers', [
            'family' => 'shoes',
            'values' => [
                'name' => [['data' => 'High-Top Sneakers', 'locale' => null, 'scope' => null]]
            ]
        ]);

        $connection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);
        $client = $this->createAuthenticatedClient(
            [],
            [],
            $connection->clientId(),
            $connection->secret(),
            $connection->username(),
            $connection->password()
        );

        $content = '';
        // Error: unknown attribute "description"
        $content .= json_encode([
            'identifier' => 'high-top_sneakers',
            'values' => [
                'description' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => 'High-Top Sneakers with white cotton laces.',
                ]]
            ]
        ]);
        $content .= PHP_EOL;
        // Success
        $content .= json_encode([
            'identifier' => 'high-top_sneakers',
            'values' => [
                'name' => [[
                    'locale' => null,
                    'scope' => null,
                    'data' => 'Vintage Sneakers'
                ]]
            ]
        ]);

        $streamedContent = '';
        ob_start(function ($buffer) use (&$streamedContent) {
            $streamedContent .= $buffer;
            return '';
        });
        $client->request(
            'PATCH',
            '/api/rest/v1/products',
            [],
            [],
            ['HTTP_content_type' => StreamResourceResponse::CONTENT_TYPE],
            $content
        );
        ob_end_flush();

        dump($streamedContent);

        // Assert::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        // $expectedContent = json_decode($streamedContent, true);

        $this->esClient->refreshIndex();
        $result = $this->esClient->search(["query" => ["term" => ["connection_code" => "erp"]]]);

        dd($result);

        //        Assert::assertEquals(1, $result['hits']['total']['value']);
        //
        //        $doc = $result['hits']['hits'][0]['_source'];
        //        Assert::assertEquals('erp', $doc['connection_code']);
        //        Assert::assertEquals($expectedContent, $doc['content']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->esClient = $this->get('akeneo_connectivity.client.connection_error');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttribute(array $data): void
    {
        $data['group'] = $data['group'] ?? 'other';

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $constraints = $this->get('validator')->validate($attribute);
        $this->assertCount(0, $constraints, 'The validation from the attribute creation failed.');
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(array $data): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update($family, $data);
        $constraints = $this->get('validator')->validate($family);
        $this->assertCount(0, $constraints, 'The validation from the family creation failed.');
        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createProduct($identifier, array $data): ProductInterface
    {
        $family = isset($data['family']) ? $data['family'] : null;

        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $family);
        $this->updateProduct($product, $data);

        return $product;
    }

    private function updateProduct(ProductInterface $product, array $data): void
    {
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $constraints = $this->get('validator')->validate($product);
        $this->assertCount(0, $constraints, 'The validation from the product creation failed.');
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }
}
