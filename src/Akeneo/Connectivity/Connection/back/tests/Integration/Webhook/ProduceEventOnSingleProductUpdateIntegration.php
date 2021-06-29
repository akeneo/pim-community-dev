<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\ProductLoader;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProduceEventOnSingleProductUpdateIntegration extends TestCase
{
    use AssertEventCountTrait;

    private ProductLoader $productLoader;
    private UniqueValuesSet $uniqueValuesSet;
    private ObjectUpdaterInterface $updater;
    private SaverInterface $saver;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');
        $this->uniqueValuesSet = $this->get('pim_catalog.validator.unique_value_set');

        $this->updater = $this->get('pim_catalog.updater.product');
        $this->saver = $this->get('pim_catalog.saver.product');
        $this->validator = $this->get('validator');
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

        $this->updater->update($product, ['enabled' => false]);
        $constraints = $this->validator->validate($product);
        Assert::assertCount(0, $constraints, 'The validation from the product creation failed.');
        $this->saver->save($product);

        $this->assertEventCount(1, ProductUpdated::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
