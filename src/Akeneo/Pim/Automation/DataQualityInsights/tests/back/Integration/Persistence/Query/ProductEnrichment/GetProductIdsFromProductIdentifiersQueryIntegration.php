<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment\GetProductIdsFromProductIdentifiersQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductIdsFromProductIdentifiersQueryIntegration extends TestCase
{
    /** @var GetProductIdsFromProductIdentifiersQuery */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetProductIdsFromProductIdentifiersQuery::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_product_ids_by_product_identifiers(): void
    {
        $this->createProduct('product_1');
        $productId2 = $this->createProduct('product_2');
        $productId3 = $this->createProduct('product_3');

        $productIds = $this->query->execute(['product_2', 'product_3']);
        $expectedProductIds = [
            'product_2' => $productId2,
            'product_3' => $productId3,
        ];

        $this->assertEquals($expectedProductIds, $productIds);
    }

    private function createProduct(string $identifier): ProductId
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier($identifier)
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId(intval($product->getId()));
    }
}
