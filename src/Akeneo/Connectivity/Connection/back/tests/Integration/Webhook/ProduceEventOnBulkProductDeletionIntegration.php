<?php

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProduceAPIEventOnBulkProductDeletionIntegration extends TestCase
{
    private ProductBuilderInterface $productBuilder;
    private BulkSaverInterface $productSaver;
    private BulkRemoverInterface $productRemover;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productBuilder = $this->get('pim_catalog.builder.product');
        $this->productSaver = $this->get('pim_catalog.saver.product');
        $this->productRemover = $this->get('pim_catalog.remover.product');
    }

    public function test_the_bulk_product_remove_event()
    {
        $count = 3;
        $products = [];
        for ($i = 0; $i < $count; $i++) {
            $products[] = $this->productBuilder->createProduct(sprintf('t-shirt-%s', $i));
        }
        $this->productSaver->saveAll($products);
        $this->productRemover->removeAll($products);

        $transport = self::$container->get('messenger.transport.business_event');

        $envelopes = $transport->get();
        $this->assertCount(2, $envelopes);

        /** @var BulkEvent */
        $bulkEvent = $envelopes[0]->getMessage();
        $this->assertInstanceOf(BulkEvent::class, $bulkEvent);
        $this->assertCount($count, $bulkEvent->getEvents());
        $this->assertContainsOnlyInstancesOf(ProductCreated::class, $bulkEvent->getEvents());

        /** @var BulkEvent */
        $bulkEvent = $envelopes[1]->getMessage();
        $this->assertInstanceOf(BulkEvent::class, $bulkEvent);
        $this->assertCount($count, $bulkEvent->getEvents());
        $this->assertContainsOnlyInstancesOf(ProductRemoved::class, $bulkEvent->getEvents());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
