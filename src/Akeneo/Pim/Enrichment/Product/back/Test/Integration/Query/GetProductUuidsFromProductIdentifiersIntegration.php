<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Enrichment\Product\Integration\Query;

use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\GetProductUuidsFromProductIdentifiers;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;

final class GetProductUuidsFromProductIdentifiersIntegration extends EnrichmentProductTestCase
{
    private GetProductUuidsFromProductIdentifiers $getProductUuidsFromProductIdentifiers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getProductUuidsFromProductIdentifiers = $this->get(GetProductUuidsFromProductIdentifiers::class);
    }

    public function testItGetsProductUuidsFromIdentifiers(): void
    {
        $this->createUser('peter', ['ROLE_USER'], ['IT support']);
        $productRepository = $this->get('pim_catalog.repository.product');

        $this->createProduct('green', []);
        $product1 = $productRepository->findOneByIdentifier('green');

        $this->createProduct('red', []);
        $product2 = $productRepository->findOneByIdentifier('red');

        $expected = [
            'green' => $product1->getUuid()->toString(),
            'red' => $product2->getUuid()->toString(),
        ];

        $result = $this->getProductUuidsFromProductIdentifiers->execute(['green', 'red']);

        $this->assertEquals($expected, $result);
    }
}
