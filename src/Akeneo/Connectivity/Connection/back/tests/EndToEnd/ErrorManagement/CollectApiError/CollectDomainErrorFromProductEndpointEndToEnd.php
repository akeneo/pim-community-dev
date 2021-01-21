<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\ProductLoader;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Structure\FamilyLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\Stream\StreamResourceResponse;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Elasticsearch\Client;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test that the same domain error is collected by each product endpoint.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CollectDomainErrorFromProductEndpointEndToEnd extends ApiTestCase
{
    /** @var FamilyLoader */
    private $familyLoader;

    /** @var ProductLoader */
    private $productLoader;

    /** @var Client */
    private $elasticsearch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->familyLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.family');
        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');

        $this->elasticsearch = $this->get('akeneo_connectivity.client.connection_error');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * Create a product with an unknown attribute code.
     */
    public function test_it_collects_a_domain_error_from_the_create_endpoint(): void
    {
        $this->familyLoader->create(['code' => 'shoes', 'attributes' => ['sku']]);

        $connection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);

        $client = $this->createAuthenticatedClient(
            [],
            [],
            $connection->clientId(),
            $connection->secret(),
            $connection->username(),
            $connection->password()
        );

        $content = json_encode([
            'identifier' => 'high-top_sneakers',
            'family' => 'shoes',
            'values' => [
                'name' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'High-Top Sneakers',
                    ]
                ]
            ]
        ]);

        $client->request('POST', '/api/rest/v1/products', [], [], [], $content);
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $this->elasticsearch->refreshIndex();
        $result = $this->elasticsearch->search([]);

        Assert::assertCount(1, $result['hits']['hits']);
    }

    /**
     * Partial update a product with an unknown attribute code.
     */
    public function test_it_collects_a_domain_error_from_the_partial_update_endpoint(): void
    {
        $this->familyLoader->create(['code' => 'shoes', 'attributes' => ['sku']]);
        $this->productLoader->create('high-top_sneakers', ['family' => 'shoes']);

        $connection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);

        $client = $this->createAuthenticatedClient(
            [],
            [],
            $connection->clientId(),
            $connection->secret(),
            $connection->username(),
            $connection->password()
        );

        $content = json_encode([
            'identifier' => 'high-top_sneakers',
            'family' => 'shoes',
            'values' => [
                'name' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'High-Top Sneakers',
                    ]
                ]
            ]
        ]);

        $client->request('PATCH', '/api/rest/v1/products/high-top_sneakers', [], [], [], $content);
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());

        $this->elasticsearch->refreshIndex();
        $result = $this->elasticsearch->search([]);

        Assert::assertCount(1, $result['hits']['hits']);
    }

    /**
     * Partial update list of one product with an unknown attribute code.
     */
    public function test_it_collects_a_domain_error_from_the_partial_update_list_endpoint(): void
    {
        $this->familyLoader->create(['code' => 'shoes', 'attributes' => ['sku']]);
        $this->productLoader->create('high-top_sneakers', ['family' => 'shoes']);

        $connection = $this->createConnection('erp', 'ERP', FlowType::DATA_SOURCE, true);

        $client = $this->createAuthenticatedClient(
            [],
            [],
            $connection->clientId(),
            $connection->secret(),
            $connection->username(),
            $connection->password()
        );

        $content = json_encode([
            'identifier' => 'high-top_sneakers',
            'values' => [
                'name' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'High-Top Sneakers',
                    ]
                ]
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

        Assert::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $this->elasticsearch->refreshIndex();
        $result = $this->elasticsearch->search([]);

        Assert::assertCount(1, $result['hits']['hits']);
    }
}
