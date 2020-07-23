<?php

namespace Akeneo\SharedCatalog\tests\back\Integration\Query;

use Akeneo\SharedCatalog\Query\GetProductIdFromProductIdentifierQueryInterface;
use Akeneo\SharedCatalog\tests\back\Utils\CreateProduct;
use Akeneo\Test\Integration\TestCase;

class GetProductIdFromProductIdentifierQueryIntegration extends TestCase
{
    use CreateProduct;

    /** @var GetProductIdFromProductIdentifierQueryInterface */
    private $getProductIdFromProductIdentifierQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getProductIdFromProductIdentifierQuery = $this->get(GetProductIdFromProductIdentifierQueryInterface::class);
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
        $expectedProductId = $product->getId();

        $result = $this->getProductIdFromProductIdentifierQuery->execute($productIdentifier);

        self::assertEquals($expectedProductId, $result);
    }

    /**
     * @test
     */
    public function it_returns_null_if_the_identifier_does_not_exists()
    {
        $result = $this->getProductIdFromProductIdentifierQuery->execute('this_does_not_exists');

        self::assertNull($result);
    }
}
