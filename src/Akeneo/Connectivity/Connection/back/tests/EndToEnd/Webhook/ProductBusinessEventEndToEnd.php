<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Webhook;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\ProductLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductBusinessEventEndToEnd extends ApiTestCase
{
    /** @var ProductLoader */
    private $productLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');
    }

    public function test_create_product_add_business_event_to_queue()
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

        $transport = self::$container->get('messenger.transport.business_event');

        $envelopes = $transport->get();
        $this->assertCount(1, $envelopes);
        $this->assertInstanceOf('\Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated', $envelopes[0]->getMessage());
    }

    public function test_update_product_add_business_event_to_queue()
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
    {"identifier": "product_update_test", "updated": "2020-10-01T11:56:12+02:00"}
JSON;

        $apiClient->request('PATCH', 'api/rest/v1/products/product_update_test', [], [], [], $data);

        $transport = self::$container->get('messenger.transport.business_event');

        $envelopes = $transport->get();
        $this->assertCount(1, $envelopes);
        $this->assertInstanceOf('\Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated', $envelopes[0]->getMessage());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
