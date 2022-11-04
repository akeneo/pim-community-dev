<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\MarkCommentsAsReadByRetailer;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseMarkCommentsAsReadByRetailerIntegration extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $suppplierRepository = $this->get(Repository::class);
        $suppplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')
                ->build(),
        );

        $supplier = new Supplier(
            'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
            'supplier_code',
            'Supplier label',
        );

        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withIdentifier('8a1c76ce-4efe-11ed-bdc3-0242ac120002')
                ->withOriginalFilename('file2.xlsx')
                ->uploadedBySupplier($supplier)
                ->uploadedAt(new \DateTimeImmutable())
                ->build(),
        );
    }

    /** @test */
    public function itMarksProductFileCommentsReadAsRetailerByInsertingANewLineWhenReadTheFirstTime(): void
    {
        $this->get(MarkCommentsAsReadByRetailer::class)('8a1c76ce-4efe-11ed-bdc3-0242ac120002', new \DateTimeImmutable('2022-10-19 10:05:30'));

        self::assertSame('2022-10-19 10:05:30', $this->findProductFilesCommentsReadDate());
    }

    /** @test */
    public function itUpdatesProductFileCommentsReadDateAsRetailer(): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_supplier_portal_product_file_comments_read_by_retailer (product_file_identifier, last_read_at)
            VALUES ('8a1c76ce-4efe-11ed-bdc3-0242ac120002', '2022-10-19 10:05:30');
        SQL;

        $this->get(Connection::class)->executeQuery($sql);

        $this->get(MarkCommentsAsReadByRetailer::class)('8a1c76ce-4efe-11ed-bdc3-0242ac120002', new \DateTimeImmutable('2022-10-20 10:05:30'));

        self::assertSame('2022-10-20 10:05:30', $this->findProductFilesCommentsReadDate());
    }

    private function findProductFilesCommentsReadDate(): ?string
    {
        $sql = <<<SQL
            SELECT last_read_at
            FROM `akeneo_supplier_portal_product_file_comments_read_by_retailer`
            WHERE product_file_identifier = '8a1c76ce-4efe-11ed-bdc3-0242ac120002';
        SQL;

        $result = $this->get(Connection::class)->executeQuery($sql)->fetchAssociative();

        if (false === $result) {
            return null;
        }

        return $result['last_read_at'];
    }
}
