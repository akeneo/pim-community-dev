<?php

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProduceAPIEventOnBulkProductModelUpdateIntegration extends TestCase
{
    private SimpleFactoryInterface $productModelFactory;
    private SaverInterface $productModelSaver;
    private ObjectUpdaterInterface $productModelUpdater;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productModelFactory = $this->get('pim_catalog.factory.product_model');
        $this->productModelSaver = $this->get('pim_catalog.saver.product_model');
        $this->productModelUpdater = $this->get('pim_catalog.updater.product_model');
    }

    public function test_the_bulk_product_model_update_event()
    {
        $count = 3;
        $productModels = [];
        for ($i = 0; $i < $count; $i++) {
            $productModel = $this->productModelFactory->create();
            $this->productModelUpdater->update($productModel, [
                'code' => sprintf('foo-%s', $i),
                'family_variant' => 'familyVariantA1',
            ]);
            $productModels[] = $productModel;
        }
        $this->productModelSaver->saveAll($productModels);

        foreach ($productModels as $productModel) {
            $this->productModelUpdater->update($productModel, [
                'values' => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionA'],
                    ]
                ],
            ]);
        }
        $this->productModelSaver->saveAll($productModels);

        $transport = self::$container->get('messenger.transport.business_event');

        $envelopes = $transport->get();
        $this->assertCount(2, $envelopes);

        /** @var BulkEvent */
        $bulkEvent = $envelopes[0]->getMessage();
        $this->assertInstanceOf(BulkEvent::class, $bulkEvent);
        $this->assertCount($count, $bulkEvent->getEvents());
        $this->assertContainsOnlyInstancesOf(ProductModelCreated::class, $bulkEvent->getEvents());

        /** @var BulkEvent */
        $bulkEvent = $envelopes[1]->getMessage();
        $this->assertInstanceOf(BulkEvent::class, $bulkEvent);
        $this->assertCount($count, $bulkEvent->getEvents());
        $this->assertContainsOnlyInstancesOf(ProductModelUpdated::class, $bulkEvent->getEvents());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
