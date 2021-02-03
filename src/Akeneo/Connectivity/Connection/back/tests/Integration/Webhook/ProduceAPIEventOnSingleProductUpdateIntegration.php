<?php

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\ProductLoader;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProduceAPIEventOnSingleProductUpdateIntegration extends TestCase
{
    private ProductLoader $productLoader;
    private UniqueValuesSet $uniqueValuesSet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');
        $this->uniqueValuesSet = $this->get('pim_catalog.validator.unique_value_set');
    }

    public function test_the_single_product_update_event()
    {
        $product = $this->productLoader->create('t-shirt', [
            'enabled' => true,
        ]);

        // When validating a product, there is an in-memory list of identifiers for uniqueness
        // We need to clear this list before updating the product, otherwise it will fail on
        // "the identifier is already used"
        $this->uniqueValuesSet->reset();

        $this->productLoader->update($product, [
            'enabled' => false,
        ]);

        $transport = self::$container->get('messenger.transport.business_event');

        $envelopes = $transport->get();
        $this->assertCount(2, $envelopes);

        /** @var BulkEvent */
        $bulkEvent = $envelopes[0]->getMessage();
        $this->assertInstanceOf(BulkEvent::class, $bulkEvent);
        $this->assertCount(1, $bulkEvent->getEvents());
        $this->assertContainsOnlyInstancesOf(ProductCreated::class, $bulkEvent->getEvents());

        /** @var BulkEvent */
        $bulkEvent = $envelopes[1]->getMessage();
        $this->assertInstanceOf(BulkEvent::class, $bulkEvent);
        $this->assertCount(1, $bulkEvent->getEvents());
        $this->assertContainsOnlyInstancesOf(ProductUpdated::class, $bulkEvent->getEvents());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
