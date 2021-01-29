<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Webhook;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\ProductLoader;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

/**
 * @author    Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProduceProductEventIntegration extends TestCase
{
    private ProductBuilderInterface $productBuilder;
    private SaverInterface $productSaver;
    private ObjectUpdaterInterface $productUpdater;
    private RemoverInterface $productRemover;
    private ProductLoader $productLoader;
    private UniqueValuesSet $uniqueValuesSet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productBuilder = $this->get('pim_catalog.builder.product');
        $this->productSaver = $this->get('pim_catalog.saver.product');
        $this->productUpdater = $this->get('pim_catalog.updater.product');
        $this->productRemover = $this->get('pim_catalog.remover.product');
        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');
        $this->uniqueValuesSet = $this->get('pim_catalog.validator.unique_value_set');
    }

    public function test_the_product_creation_event()
    {
        $product = $this->productBuilder->createProduct('t-shirt');
        $this->productSaver->save($product);

        $transport = self::$container->get('messenger.transport.business_event');

        $envelopes = $transport->get();
        $this->assertCount(1, $envelopes);

        /** @var BulkEvent */
        $bulkEvent = $envelopes[0]->getMessage();
        $this->assertInstanceOf(BulkEvent::class, $bulkEvent);
        $this->assertCount(1, $bulkEvent->getEvents());
        $this->assertContainsOnlyInstancesOf(ProductCreated::class, $bulkEvent->getEvents());
    }

    public function test_the_product_update_event()
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

    public function test_the_product_remove_event()
    {
        $product = $this->productLoader->create('t-shirt', []);
        $this->productRemover->remove($product);

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
        $this->assertContainsOnlyInstancesOf(ProductRemoved::class, $bulkEvent->getEvents());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
