<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFilesForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;

final class DatabaseListProductFilesForSupplierIntegration extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $suppplierRepository = $this->get(Repository::class);
        $suppplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')
                ->withCode('supplier_1')
                ->withContributors(['contributor1@example.com', 'contributor2@example.com'])
                ->build(),
        );
        $suppplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('951c7717-8316-42e7-b053-61f265507178')
                ->withCode('supplier_2')
                ->build(),
        );
    }

    /** @test */
    public function itGetsNothingIfThereIsNoProductFilesForAGivenContributorAndTheContributorsBelongingToTheSameSupplier(): void
    {
        static::assertEmpty(($this->get(ListProductFilesForSupplier::class))('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7'));
    }

    /** @test */
    public function itGetsTheLatestTwentyFiveProductFilesForAGivenContributorAndTheContributorsBelongingToTheSameSupplier(): void
    {
        $this->createProductFiles();

        $sut = $this->get(ListProductFilesForSupplier::class);

        $supplierProductFiles = ($sut)('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7');

        $expectedProductFilenames = [];
        for ($i = 0; 25 > $i; $i++) {
            $expectedProductFilenames[] = sprintf('products_%d.xlsx', $i+1);
        }

        static::assertEqualsCanonicalizing(
            $expectedProductFilenames,
            array_map(
                fn (ProductFile $supplierProductFile) => $supplierProductFile->originalFilename,
                $supplierProductFiles,
            ),
        );
    }

    /** @test */
    public function itGetsTheProductFilesWithTheirComments(): void
    {
        $this->createProductFile('5d001a43-a42d-4083-8673-b64bb4ecd26f');
        $this->createRetailerComment(
            '5d001a43-a42d-4083-8673-b64bb4ecd26f',
            'julia@roberts.com',
            'Your product file is awesome!',
            new \DateTimeImmutable('2022-09-07 00:00:00'),
        );
        $this->createSupplierComment(
            '5d001a43-a42d-4083-8673-b64bb4ecd26f',
            'jimmy@punchline.com',
            'Here are the products I\'ve got for you.',
            new \DateTimeImmutable('2022-09-07 00:00:01'),
        );

        $sut = $this->get(ListProductFilesForSupplier::class);
        /** @var ProductFile[] $productFiles */
        $productFiles = ($sut)('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7');

        static::assertCount(1, $productFiles);
        static::assertSame('5d001a43-a42d-4083-8673-b64bb4ecd26f', $productFiles[0]->identifier);
        static::assertSame('file.xlsx', $productFiles[0]->originalFilename);
        static::assertSame('path/to/file.xlsx', $productFiles[0]->path);
        static::assertSame('jimmy@punchline.com', $productFiles[0]->uploadedByContributor);
        static::assertSame('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', $productFiles[0]->uploadedBySupplier);
        static::assertSame('2022-09-07 08:54:38', $productFiles[0]->uploadedAt);
        static::assertCount(1, $productFiles[0]->retailerComments);
        static::assertCount(1, $productFiles[0]->supplierComments);
        static::assertEquals([[
            'content' => 'Your product file is awesome!' ,
            'author_email' => 'julia@roberts.com',
            'created_at' => '2022-09-07 00:00:00.000000',
        ]], $productFiles[0]->retailerComments);
        static::assertEquals([[
            'content' => 'Here are the products I\'ve got for you.' ,
            'author_email' => 'jimmy@punchline.com',
            'created_at' => '2022-09-07 00:00:01.000000',
        ]], $productFiles[0]->supplierComments);
    }

    private function createProductFiles(): void
    {
        $this->get(Connection::class)->executeStatement(
            <<<SQL
            INSERT INTO akeneo_supplier_portal_supplier_product_file (
                identifier, 
                original_filename, 
                path, 
                uploaded_by_contributor, 
                uploaded_by_supplier, 
                uploaded_at
            ) VALUES (
                :identifier,
                :original_filename,
                :path,
                :contributorEmail,
                :supplierIdentifier,
                :uploadedAt
            )
        SQL,
            [
                'identifier' => Uuid::uuid4()->toString(),
                'original_filename' => 'products_file_from_another_supplier.xlsx',
                'path' => sprintf(
                    'supplier2/%s-products_file_from_another_supplier.xlsx',
                    Uuid::uuid4()->toString(),
                ),
                'contributorEmail' => 'contributor-belonging-to-another-supplier@example.com',
                'supplierIdentifier' => '951c7717-8316-42e7-b053-61f265507178',
                'uploadedAt' => (new \DateTimeImmutable())->add(
                    \DateInterval::createFromDateString(sprintf('1 year')),
                )->format('Y-m-d H:i:s'),
            ],
        );

        for ($i = 0; 30 > $i; $i++) {
            $sql = <<<SQL
                INSERT INTO akeneo_supplier_portal_supplier_product_file (
                    identifier, 
                    original_filename, 
                    path, 
                    uploaded_by_contributor, 
                    uploaded_by_supplier, 
                    uploaded_at
                ) VALUES (
                    :identifier,
                    :originalFilename,
                    :path,
                    :contributorEmail,
                    :supplierIdentifier,
                    :uploadedAt
                )
            SQL;

            $this->get(Connection::class)->executeStatement(
                $sql,
                [
                    'identifier' => Uuid::uuid4()->toString(),
                    'originalFilename' => sprintf('products_%d.xlsx', $i+1),
                    'path' => sprintf('supplier1/%s-products_1.xlsx', Uuid::uuid4()->toString()),
                    'contributorEmail' => $i % 2 ? 'contributor1@example.com' : 'contributor2@example.com',
                    'supplierIdentifier' => 'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                    'uploadedAt' => (new \DateTimeImmutable())->add(
                        \DateInterval::createFromDateString(sprintf('%d minutes', 30 - $i)),
                    )->format('Y-m-d H:i:s'),
                ],
            );
        }
    }

    private function createProductFile(string $productFileIdentifier): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_supplier_product_file` (
                identifier,
                original_filename,
                path,
                uploaded_by_contributor,
                uploaded_by_supplier,
                uploaded_at
            ) VALUES (:identifier, :originalFilename, :path, :contributorEmail, :supplierIdentifier, :uploadedAt)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => $productFileIdentifier,
                'originalFilename' => 'file.xlsx',
                'path' => 'path/to/file.xlsx',
                'contributorEmail' => 'jimmy@punchline.com',
                'supplierIdentifier' => 'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                'uploadedAt' => '2022-09-07 08:54:38',
            ],
        );
    }

    private function createRetailerComment(
        string $productFileIdentifier,
        string $authorEmail,
        string $content,
        \DateTimeImmutable $createdAt,
    ): void {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_product_file_retailer_comments` (
                author_email,
                product_file_identifier,
                content,
                created_at
            ) VALUES (:authorEmail, :productFileIdentifier, :content, :createdAt);
        SQL;

        $this->get(Connection::class)->executeStatement(
            $sql,
            [
                'authorEmail' => $authorEmail,
                'productFileIdentifier' => $productFileIdentifier,
                'content' => $content,
                'createdAt' => $createdAt,
            ],
            [
                'createdAt' => Types::DATETIME_IMMUTABLE,
            ],
        );
    }

    private function createSupplierComment(
        string $productFileIdentifier,
        string $authorEmail,
        string $content,
        \DateTimeImmutable $createdAt,
    ): void {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_product_file_supplier_comments` (
                author_email,
                product_file_identifier,
                content,
                created_at
            ) VALUES (:authorEmail, :productFileIdentifier, :content, :createdAt);
        SQL;

        $this->get(Connection::class)->executeStatement(
            $sql,
            [
                'authorEmail' => $authorEmail,
                'productFileIdentifier' => $productFileIdentifier,
                'content' => $content,
                'createdAt' => $createdAt,
            ],
            [
                'createdAt' => Types::DATETIME_IMMUTABLE,
            ],
        );
    }
}
