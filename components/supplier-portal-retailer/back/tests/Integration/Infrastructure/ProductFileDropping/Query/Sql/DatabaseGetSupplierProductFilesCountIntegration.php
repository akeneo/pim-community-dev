<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetSupplierProductFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\ProductFileImportRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileImportBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Ramsey\Uuid\Uuid;

final class DatabaseGetSupplierProductFilesCountIntegration extends SqlIntegrationTestCase
{
    private string $supplier1Identifier;
    private string $supplier2Identifier;

    protected function setUp(): void
    {
        parent::setUp();

        $supplierRepository = $this->get(Repository::class);
        $this->supplier1Identifier = Uuid::uuid4()->toString();
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier($this->supplier1Identifier)
                ->withCode('supplier_1')
                ->build(),
        );
        $supplier1 = new Supplier(
            $this->supplier1Identifier,
            'supplier_1',
            'Supplier 1 label',
        );

        $this->supplier2Identifier = Uuid::uuid4()->toString();
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier($this->supplier2Identifier)
                ->withCode('supplier_2')
                ->build(),
        );
        $supplier2 = new Supplier(
            $this->supplier2Identifier,
            'supplier_2',
            'Supplier 2 label',
        );

        $productFileRepository = $this->get(ProductFileRepository::class);

        for ($i = 1; 15 >= $i; $i++) {
            $productFileRepository->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($supplier1)
                    ->withOriginalFilename('file'.$i)
                    ->build(),
            );
        }

        for ($i = 1; 10 >= $i; $i++) {
            $productFileRepository->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($supplier2)
                    ->build(),
            );
        }
    }

    /** @test */
    public function itReturns0IfThereIsNoFile(): void
    {
        static::assertSame(0, $this->get(GetSupplierProductFilesCount::class)(Uuid::uuid4()->toString()));
    }

    /** @test */
    public function itReturnsTheNumberOfProductFilesForASupplierWithoutSearch(): void
    {
        static::assertSame(15, $this->get(GetSupplierProductFilesCount::class)($this->supplier1Identifier));
    }

    /** @test */
    public function itReturnsTheNumberOfProductFilesForASupplierWithSearch(): void
    {
        static::assertSame(7, $this->get(GetSupplierProductFilesCount::class)($this->supplier1Identifier, '1'));
    }

    /** @test */
    public function itCanCountProductFilesDependingOnStatus(): void
    {
        $supplierRepository = $this->get(Repository::class);
        $supplier1Identifier = Uuid::uuid4()->toString();
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier($supplier1Identifier)
                ->withCode('supplier_code_1')
                ->build(),
        );
        $supplier1 = new Supplier(
            $supplier1Identifier,
            'supplier_code_1',
            'Supplier label',
        );

        $supplier2Identifier = Uuid::uuid4()->toString();
        $supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier($supplier2Identifier)
                ->withCode('supplier_code_2')
                ->build(),
        );
        $supplier2 = new Supplier(
            $supplier2Identifier,
            'supplier_code_2',
            'Supplier label',
        );

        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($supplier1)
                ->build(),
        );
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->uploadedBySupplier($supplier1)
                ->build(),
        );

        $productFile1 = (new ProductFileBuilder())
            ->withIdentifier(Uuid::uuid4()->toString())
            ->uploadedBySupplier($supplier2)
            ->build();
        $productFileRepository->save($productFile1);
        $productFileImport1 = (new ProductFileImportBuilder())
            ->withProductFile($productFile1)
            ->withImportExecutionId(1)
            ->withImportStatus(ProductFileImportStatus::IN_PROGRESS)
            ->build();
        ($this->get(ProductFileImportRepository::class))->save($productFileImport1);

        $productFile2 = (new ProductFileBuilder())
            ->withIdentifier(Uuid::uuid4()->toString())
            ->uploadedBySupplier($supplier1)
            ->build();
        $productFileRepository->save($productFile2);
        $productFileImport2 = (new ProductFileImportBuilder())
            ->withProductFile($productFile2)
            ->withImportExecutionId(2)
            ->withImportStatus(ProductFileImportStatus::COMPLETED)
            ->build();
        ($this->get(ProductFileImportRepository::class))->save($productFileImport2);

        $productFile3 = (new ProductFileBuilder())
            ->withIdentifier(Uuid::uuid4()->toString())
            ->uploadedBySupplier($supplier2)
            ->build();
        $productFileRepository->save($productFile3);
        $productFileImport3 = (new ProductFileImportBuilder())
            ->withProductFile($productFile3)
            ->withImportExecutionId(3)
            ->withImportStatus(ProductFileImportStatus::FAILED)
            ->build();
        ($this->get(ProductFileImportRepository::class))->save($productFileImport3);

        static::assertSame(
            2,
            $this->get(GetSupplierProductFilesCount::class)(
                $supplier1Identifier,
                '',
                ProductFileImportStatus::TO_IMPORT
            ),
        );
        static::assertSame(
            1,
            $this->get(GetSupplierProductFilesCount::class)(
                $supplier2Identifier,
                '',
                ProductFileImportStatus::IN_PROGRESS
            ),
        );
        static::assertSame(
            1,
            $this->get(GetSupplierProductFilesCount::class)(
                $supplier1Identifier,
                '',
                ProductFileImportStatus::COMPLETED
            ),
        );
        static::assertSame(
            1,
            $this->get(GetSupplierProductFilesCount::class)(
                $supplier2Identifier,
                '',
                ProductFileImportStatus::FAILED
            ),
        );
        static::assertSame(3, $this->get(GetSupplierProductFilesCount::class)($supplier1Identifier));
        static::assertSame(2, $this->get(GetSupplierProductFilesCount::class)($supplier2Identifier));
    }
}
