<?php

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductModelUpdater;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProduceAPIEventOnSingleProductModelDeletionIntegration extends TestCase
{
    private SimpleFactoryInterface $productModelFactory;
    private SaverInterface $productModelSaver;
    private ProductModelUpdater $productModelUpdater;
    private RemoverInterface $productModelRemover;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productModelFactory = $this->get('pim_catalog.factory.product_model');
        $this->productModelSaver = $this->get('pim_catalog.saver.product_model');
        $this->productModelUpdater = $this->get('pim_catalog.updater.product_model');
        $this->productModelRemover = $this->get('pim_catalog.remover.product_model');
    }

    public function test_the_single_product_model_deletion_event()
    {
        $productModel = $this->productModelFactory->create();
        $this->productModelUpdater->update($productModel, [
            'code' => 'foo',
            'family_variant' => 'familyVariantA1',
        ]);
        $this->productModelSaver->save($productModel);
        $this->productModelRemover->remove($productModel);

        $transport = self::$container->get('messenger.transport.business_event');

        $envelopes = $transport->get();
        $this->assertCount(2, $envelopes);

        /** @var BulkEvent */
        $bulkEvent = $envelopes[0]->getMessage();
        $this->assertInstanceOf(BulkEvent::class, $bulkEvent);
        $this->assertCount(1, $bulkEvent->getEvents());
        $this->assertContainsOnlyInstancesOf(ProductModelCreated::class, $bulkEvent->getEvents());

        /** @var BulkEvent */
        $bulkEvent = $envelopes[1]->getMessage();
        $this->assertInstanceOf(BulkEvent::class, $bulkEvent);
        $this->assertCount(1, $bulkEvent->getEvents());
        $this->assertContainsOnlyInstancesOf(ProductModelRemoved::class, $bulkEvent->getEvents());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
