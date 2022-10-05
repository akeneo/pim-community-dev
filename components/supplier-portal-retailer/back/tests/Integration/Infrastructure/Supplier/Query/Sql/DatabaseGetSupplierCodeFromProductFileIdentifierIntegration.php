<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierCodeFromProductFileIdentifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetSupplierCodeFromProductFileIdentifierIntegration extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('a3aac0e2-9eb9-4203-8af2-5425b2062ad4')
                ->build(),
        );
    }

    /** @test */
    public function itGetsSupplierCodeFromProductFileIdentifier(): void
    {
        ($this->get(ProductFileRepository::class))->save(
            (new ProductFileBuilder())
                ->withUploadedBySupplier('a3aac0e2-9eb9-4203-8af2-5425b2062ad4')
                ->withIdentifier('ede6024b-bdce-47d0-ba0c-7132f217992f')
                ->build(),
        );

        $supplierCode = ($this->get(GetSupplierCodeFromProductFileIdentifier::class))('ede6024b-bdce-47d0-ba0c-7132f217992f');

        static::assertSame('supplier_code', $supplierCode);
    }

    /** @test */
    public function itReturnsNullIfThereIsNoFileForTheGivenProductFileIdentifier(): void
    {
        ($this->get(ProductFileRepository::class))->save(
            (new ProductFileBuilder())
                ->withIdentifier('3f91df5e-986d-43de-99b0-113bfdae7a77')
                ->build(),
        );

        $supplierCode = ($this->get(GetSupplierCodeFromProductFileIdentifier::class))('606abe11-353f-470c-aa1c-7f9e793b29a0');

        static::assertNull($supplierCode);
    }
}
