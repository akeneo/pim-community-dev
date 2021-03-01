<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProduceEventOnSingleProductModelDeletionIntegration extends TestCase
{
    use AssertEventCountTrait;

    private SimpleFactoryInterface $productModelFactory;
    private SaverInterface $productModelSaver;
    private ObjectUpdaterInterface $productModelUpdater;
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

        $this->assertEventCount(1, ProductModelRemoved::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
