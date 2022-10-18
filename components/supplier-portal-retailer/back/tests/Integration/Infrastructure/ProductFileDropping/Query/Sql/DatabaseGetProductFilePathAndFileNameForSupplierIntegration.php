<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileNameForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetProductFilePathAndFileNameForSupplierIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itDoesNotGetTheFilenameAndThePathIfTheProductFileIdentifierHasNotBeenUploadedByOneOfTheContributorsOfTheSupplierTheContributorConnectedBelongsTo(): void
    {
        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withCode('supplier_1')
                ->withContributors(['contributor+supplier1@example.com'])
                ->build(),
        );
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('bb2241e8-5242-4dbb-9d20-5e4e38514566')
                ->withCode('supplier_2')
                ->withContributors(['contributor+supplier2@example.com'])
                ->build(),
        );

        ($this->get(ProductFileRepository::class))->save(
            (new ProductFileBuilder())
                ->withIdentifier('de42d046-fd5a-4254-b5d5-bda2cb6543d2')
                ->uploadedBySupplier(
                    new Supplier(
                        'bb2241e8-5242-4dbb-9d20-5e4e38514566',
                        'supplier_2',
                        'Supplier label',
                    ),
                )
                ->build(),
        );

        static::assertNull(
            ($this->get(GetProductFilePathAndFileNameForSupplier::class))(
                'de42d046-fd5a-4254-b5d5-bda2cb6543d2',
                '44ce8069-8da1-4986-872f-311737f46f00',
            ),
        );
    }

    /** @test */
    public function itGetsTheFilenameAndThePathForProductFilesIOrMyTeammatesDropped(): void
    {
        $supplierRepository = $this->get(Repository::class);
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->withContributors(['contributor1+supplier1@example.com', 'contributor2+supplier1@example.com'])
                ->build(),
        );

        ($this->get(ProductFileRepository::class))->save(
            (new ProductFileBuilder())
                ->withContributorEmail('contributor2+supplier1@example.com')
                ->uploadedBySupplier(
                    new Supplier(
                        '44ce8069-8da1-4986-872f-311737f46f00',
                        'supplier_code',
                        'Supplier label',
                    ),
                )
                ->build(),
        );

        static::assertNull(
            ($this->get(GetProductFilePathAndFileNameForSupplier::class))(
                'de42d046-fd5a-4254-b5d5-bda2cb6543d2',
                '44ce8069-8da1-4986-872f-311737f46f00',
            ),
        );
    }
}
