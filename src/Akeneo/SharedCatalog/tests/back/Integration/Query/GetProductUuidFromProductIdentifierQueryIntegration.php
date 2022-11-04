<?php

namespace Akeneo\SharedCatalog\tests\back\Integration\Query;

use Akeneo\SharedCatalog\Query\GetProductUuidFromProductIdentifierQueryInterface;
use Akeneo\SharedCatalog\tests\back\Utils\CreateProduct;
use Akeneo\Test\Integration\TestCase;

class GetProductUuidFromProductIdentifierQueryIntegration extends TestCase
{
    use CreateProduct;

    private GetProductUuidFromProductIdentifierQueryInterface $getProductUuidFromProductIdentifierQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getProductUuidFromProductIdentifierQuery = $this->get(GetProductUuidFromProductIdentifierQueryInterface::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @test
     */
    public function it_returns_the_product_id_from_an_existing_identifier()
    {
        $productIdentifier = 'product_A';
        $product = $this->createProduct(
            $productIdentifier,
            'aFamily',
            []
        );
        $result = $this->getProductUuidFromProductIdentifierQuery->execute($productIdentifier);

        self::assertTrue($product->getUuid()->equals($result));
    }

    /**
     * @test
     */
    public function it_returns_null_if_the_identifier_does_not_exists()
    {
        $result = $this->getProductUuidFromProductIdentifierQuery->execute('this_does_not_exists');

        self::assertNull($result);
    }
}
