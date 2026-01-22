<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Ramsey\Uuid\UuidInterface;

/**
 * Test products have been correctly removed from the index after a product has been removed.
 * They should be removed from 2 indexes:
 *      - pim_catalog_product
 *      - pim_catalog_product_and_product_model
 */
class RemovingProductIntegration extends TestCase
{
    private const DOCUMENT_TYPE = 'pim_catalog_product';

    /** @var Client */
    private $esProductAndProductModelClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->esProductAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    public function testRemovingProductOnUnitaryRemove()
    {
        $product = $this->createProduct('bat');
        $productUuid = $product->getUuid();

        $this->get('pim_catalog.remover.product')->remove($product);

        $this->assertNotFoundInProductAndProductModelIndex($productUuid);
    }

    public function testRemovingProductsOnBulkRemove()
    {

        $productFoo = $this->createProduct('foo');
        $productBar = $this->createProduct('bar');
        $productBaz = $this->createProduct('baz');
        $productFooUuid = $productFoo->getUuid();
        $productBarUuid = $productBar->getUuid();
        $productBazUuid = $productBaz->getUuid();

        $this->get('pim_catalog.remover.product')->removeAll([$productFoo, $productBar, $productBaz]);

        $this->assertNotFoundInProductAndProductModelIndex($productFooUuid);
        $this->assertNotFoundInProductAndProductModelIndex($productBarUuid);
        $this->assertNotFoundInProductAndProductModelIndex($productBazUuid);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * Asserts the given productId does not exists in the product and product model Index.
     */
    private function assertNotFoundInProductAndProductModelIndex(UuidInterface $productUuid): void
    {
        $found = true;
        try {
            $this->esProductAndProductModelClient->get(self::DOCUMENT_TYPE, 'product_' . $productUuid->toString());
        } catch (ClientResponseException $e) {
            if ($e->getCode() === 404) {
                $found = false;
            } else {
                throw $e;
            }
        }
        $this->assertFalse($found);
    }

    private function createProduct(string $identifier): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier, //'bat',
            userIntents: []
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }
}
