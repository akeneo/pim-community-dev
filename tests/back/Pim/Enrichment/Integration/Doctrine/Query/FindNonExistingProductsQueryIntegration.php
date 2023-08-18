<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductsQueryInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Test\Integration\TestCase;

class FindNonExistingProductsQueryIntegration extends TestCase
{
    private FindNonExistingProductsQueryInterface $findNonExistingProductsQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createAdminUser();
        $this->findNonExistingProductsQuery = $this->get(
            'akeneo.pim.enrichment.product.query.find_non_existing_products_query'
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @test
     */
    public function it_return_nothing_when_nothing_passed()
    {
        self::assertEquals([], $this->findNonExistingProductsQuery->byProductIdentifiers([]));
    }

    /**
     * @test
     */
    public function it_returns_the_product_identifiers_that_does_not_exists()
    {
        $this->createProduct('product_1');
        $this->createProduct('product_2');
        $this->createProduct('product_3');
        $this->createProduct('product_4');
        $this->createProduct('product_5');

        $lookupProductIdentifiers = [
            'product_1',
            'product_2',
            'product_3',
            'product_does_not_exists',
        ];

        $actualNonExistingProductIdentifiers = $this->findNonExistingProductsQuery->byProductIdentifiers(
            $lookupProductIdentifiers
        );
        $expectedNonExistingProductIdentifiers = [
            'product_does_not_exists',
        ];

        self::assertEquals(
            $actualNonExistingProductIdentifiers,
            $expectedNonExistingProductIdentifiers
        );
    }

    private function createProduct(string $productIdentifier): void
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $productIdentifier,
            userIntents: []
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
    }
}
