<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\ProductLoader;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProduceEventOnSingleProductDeletionIntegration extends TestCase
{
    use AssertEventCountTrait;

    private RemoverInterface $productRemover;
    private ProductLoader $productLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRemover = $this->get('pim_catalog.remover.product');
        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');
    }

    public function test_the_single_product_remove_event()
    {
        $product = $this->productLoader->create('t-shirt', []);
        $this->productRemover->remove($product);

        $this->assertEventCount(1, ProductRemoved::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
