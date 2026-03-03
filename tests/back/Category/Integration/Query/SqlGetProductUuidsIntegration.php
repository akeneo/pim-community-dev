<?php

declare(strict_types=1);

namespace AkeneoTest\Category\Integration\Query;

use Akeneo\Pim\Enrichment\Product\Infrastructure\Query\SqlGetProductUuids;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;

final class SqlGetProductUuidsIntegration extends EnrichmentProductTestCase
{
    private $uuid1;
    private $uuid2;
    private SqlGetProductUuids $sqlGetProductUuids;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sqlGetProductUuids = $this->get('Akeneo\Pim\Enrichment\Product\Infrastructure\Query\SqlGetProductUuids');
        $this->createProduct('product1', []);
        $this->createProduct('product2', []);
        $this->uuid1 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product1')->getUuid();
        $this->uuid2 = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product2')->getUuid();
    }

    /** @test */
    public function it_returns_uuid_for_product_identifier()
    {
        Assert::assertEquals($this->uuid1, $this->sqlGetProductUuids->fromIdentifier('product1'));
    }

    /** @test */
    public function it_returns_null_for_non_existing_product()
    {
        Assert::assertNull($this->sqlGetProductUuids->fromIdentifier('non_existing_product'));
    }

    /** @test */
    public function it_returns_uuids_by_identifiers_for_product_uuids()
    {
        Assert::assertEqualsCanonicalizing(
            [
                'product1' => $this->uuid1,
                "product2" => $this->uuid2
            ],
            $this->sqlGetProductUuids->fromIdentifiers(['product1', 'non_existing_product', 'product2'])
        );
    }
}
