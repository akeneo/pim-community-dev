<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Connectivity\Connection\tests\EndToEnd;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\ProductLoader;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;

class PublishedProductDoesNotProducePimEventEndToEnd extends ApiTestCase
{
    use AssertEventCountTrait;

    private ProductLoader $productLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');
    }

    public function test_publishing_a_product_does_not_add_business_event_to_queue()
    {
        $apiConnectionEcommerce = $this->createConnection('ecommerce', 'Ecommerce', FlowType::DATA_DESTINATION);
        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $apiConnectionEcommerce->clientId(),
            $apiConnectionEcommerce->secret(),
            $apiConnectionEcommerce->username(),
            $apiConnectionEcommerce->password()
        );

        $data =
            <<<JSON
    {"identifier": "product_create_test"}
JSON;

        $apiClient->request('POST', 'api/rest/v1/products', [], [], [], $data);

        $this->assertEventCount(1, ProductCreated::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
