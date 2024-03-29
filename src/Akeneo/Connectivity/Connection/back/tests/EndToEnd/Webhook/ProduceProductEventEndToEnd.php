<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\ProductLoader;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProduceProductEventEndToEnd extends ApiTestCase
{
    use AssertEventCountTrait;

    private ProductLoader $productLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');
    }

    public function test_create_product_add_business_event_to_queue(): void
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

    public function test_update_product_add_business_event_to_queue(): void
    {
        $this->productLoader->create('product_update_test', []);

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
    {"identifier": "another_product_update_test"}
JSON;

        $apiClient->request('PATCH', 'api/rest/v1/products/product_update_test', [], [], [], $data);

        $this->assertEventCount(1, ProductUpdated::class);
    }

    public function test_remove_product_add_business_event_to_queue(): void
    {
        $this->productLoader->create('product_to_remove_test', []);

        $apiConnectionEcommerce = $this->createConnection('ecommerce', 'Ecommerce', FlowType::DATA_DESTINATION);
        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $apiConnectionEcommerce->clientId(),
            $apiConnectionEcommerce->secret(),
            $apiConnectionEcommerce->username(),
            $apiConnectionEcommerce->password()
        );

        $apiClient->request('DELETE', 'api/rest/v1/products/product_to_remove_test');

        $this->assertEventCount(1, ProductRemoved::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
