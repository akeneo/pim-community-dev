<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProduceEventOnBulkProductUpdateIntegration extends TestCase
{
    use AssertEventCountTrait;

    private ProductBuilderInterface $productBuilder;
    private BulkSaverInterface $productSaver;
    private ObjectUpdaterInterface $productUpdater;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productBuilder = $this->get('pim_catalog.builder.product');
        $this->productSaver = $this->get('pim_catalog.saver.product');
        $this->productUpdater = $this->get('pim_catalog.updater.product');
    }

    public function test_the_bulk_product_update_event()
    {
        $count = 3;
        $products = [];
        for ($i = 0; $i < $count; $i++) {
            $products[] = $this->productBuilder->createProduct(sprintf('t-shirt-%s', $i));
        }
        $this->productSaver->saveAll($products);

        foreach ($products as $product) {
            $this->productUpdater->update($product, [
                'enabled' => false,
            ]);
        }
        $this->productSaver->saveAll($products);

        $this->assertEventCount($count, ProductUpdated::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
