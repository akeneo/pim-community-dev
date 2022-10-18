<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;

final class DatabaseGetProductFilePathAndFileNameIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itReturnsNullIfTheProductFileDoesNotExist(): void
    {
        $this->assertNull($this->get(GetProductFilePathAndFileName::class)('unknown-file'));
    }

    /** @test */
    public function itGetsTheFilenameAndThePathFromAFileIdentifier(): void
    {
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('44ce8069-8da1-4986-872f-311737f46f00')
                ->build(),
        );

        ($this->get(ProductFileRepository::class))->save(
            (new ProductFileBuilder())
                ->withIdentifier('ad54830a-aeae-4b57-8313-679a2327c5f7')
                ->uploadedBySupplier(
                    new Supplier(
                        '44ce8069-8da1-4986-872f-311737f46f00',
                        'supplier_code',
                        'Supplier label',
                    ),
                )
                ->build(),
        );

        $productFile = ($this->get(GetProductFilePathAndFileName::class))('ad54830a-aeae-4b57-8313-679a2327c5f7');

        static::assertSame('file.xlsx', $productFile->originalFilename);
        static::assertSame('path/to/file.xlsx', $productFile->path);
    }
}
