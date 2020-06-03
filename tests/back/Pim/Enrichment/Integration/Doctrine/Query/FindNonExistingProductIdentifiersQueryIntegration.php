<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Doctrine\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindNonExistingProductIdentifiersQueryInterface;
use Akeneo\Test\Integration\TestCase;

class FindNonExistingProductIdentifiersQueryIntegration extends TestCase
{
    /** @var FindNonExistingProductIdentifiersQueryInterface */
    private $findNonExistingProductIdentifiersQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->findNonExistingProductIdentifiersQuery = $this->get(
            'akeneo.pim.enrichment.product.query.find_non_existing_product_identifiers_query'
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
        self::assertEquals([], $this->findNonExistingProductIdentifiersQuery->execute([]));
    }

    /**
     * @test
     */
    public function it_returns_the_product_identifiers_that_does_not_exists()
    {
        $existingProductIdentifiers = [
            'product_1',
            'product_2',
            'product_3',
            'product_4',
            'product_5',
        ];

        foreach ($existingProductIdentifiers as $productIdentifier) {
            $this->createProduct($productIdentifier);
        }

        $lookupProductIdentifiers = [
            'product_1',
            'product_2',
            'product_3',
            'product_does_not_exists',
        ];

        $actualNonExistingProductIdentifiers = $this->findNonExistingProductIdentifiersQuery->execute(
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
        $product = new Product();
        $this->get('pim_catalog.updater.product')->update(
            $product,
            [
                'values' => [
                    'sku' => [
                        [
                            'scope' => null,
                            'locale' => null,
                            'data' => $productIdentifier,
                        ],
                    ],
                ],
            ]
        );

        $this->get('pim_catalog.saver.product')->save($product);
    }
}
