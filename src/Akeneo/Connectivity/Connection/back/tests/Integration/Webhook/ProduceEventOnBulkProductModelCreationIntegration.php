<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProduceEventOnBulkProductModelCreationIntegration extends TestCase
{
    use AssertEventCountTrait;

    private SimpleFactoryInterface $productModelFactory;
    private BulkSaverInterface $productModelSaver;
    private ObjectUpdaterInterface $productModelUpdater;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productModelFactory = $this->get('pim_catalog.factory.product_model');
        $this->productModelSaver = $this->get('pim_catalog.saver.product_model');
        $this->productModelUpdater = $this->get('pim_catalog.updater.product_model');
    }

    public function test_the_bulk_product_model_creation_event()
    {
        $count = 3;
        $productModels = [];
        for ($i = 0; $i < $count; $i++) {
            $productModel = $this->productModelFactory->create();
            $this->productModelUpdater->update($productModel, [
                'code' => \sprintf('foo-%s', $i),
                'family_variant' => 'familyVariantA1',
            ]);
            $productModels[] = $productModel;
        }
        $this->productModelSaver->saveAll($productModels);

        $this->assertEventCount($count, ProductModelCreated::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
