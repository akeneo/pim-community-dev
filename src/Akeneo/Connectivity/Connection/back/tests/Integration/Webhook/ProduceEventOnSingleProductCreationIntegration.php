<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProduceEventOnSingleProductCreationIntegration extends TestCase
{
    use AssertEventCountTrait;

    private ProductBuilderInterface $productBuilder;
    private SaverInterface $productSaver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productBuilder = $this->get('pim_catalog.builder.product');
        $this->productSaver = $this->get('pim_catalog.saver.product');
    }

    public function test_the_single_product_creation_event()
    {
        $product = $this->productBuilder->createProduct('t-shirt');
        $this->productSaver->save($product);

        $this->assertEventCount(1, ProductCreated::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
