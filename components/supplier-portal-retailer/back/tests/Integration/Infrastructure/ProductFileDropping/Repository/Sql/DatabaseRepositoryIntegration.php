<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Repository\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseRepositoryIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itSavesAProductFile(): void
    {
        $this->createSupplier();
        $repository = $this->get(ProductFileRepository::class);
        $productFile = ProductFile::create(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'product-file.xlsx',
            '1/2/3/4/product-file.xlsx',
            'contributor@example.com',
            new Supplier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', 'los_pollos_hermanos', 'Los Pollos Hermanos'),
        );
        $repository->save($productFile);

        $savedProductFile = $this->findProductFile('product-file.xlsx');

        $this->assertSame($productFile->originalFilename(), $savedProductFile['original_filename']);
        $this->assertSame($productFile->path(), $savedProductFile['path']);
        $this->assertSame($productFile->contributorEmail(), $savedProductFile['uploaded_by_contributor']);
        $this->assertSame($productFile->supplierIdentifier(), $savedProductFile['uploaded_by_supplier']);
        $this->assertFalse((bool) $savedProductFile['downloaded']);
    }

    /** @test */
    public function itSavesRetailerCommentsForAProductFile(): void
    {
        $this->createSupplier();
        $repository = $this->get(ProductFileRepository::class);
        $productFile = ProductFile::create(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'product-file.xlsx',
            '1/2/3/4/product-file.xlsx',
            'jimmy@punchline.com',
            new Supplier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', 'jimmy_punchline', 'Jimmy Punchline'),
        );
        $firstCommentCreatedAt = new \DateTimeImmutable('2022-09-08 17:02:52');
        $productFile->addNewRetailerComment(
            'Your product file is garbage!',
            'julia@roberts.com',
            $firstCommentCreatedAt,
        );
        $secondCommentCreatedAt = new \DateTimeImmutable('2022-09-08 17:02:53');
        $productFile->addNewRetailerComment(
            'I\'m kidding, it\'s awesome!',
            'julia@roberts.com',
            $secondCommentCreatedAt,
        );

        $repository->save($productFile);

        $comments = $this->findRetailerCommentsForProductFile('b8b13d0b-496b-4a7c-a574-0d522ba90752');

        static::assertCount(2, $comments);
        static::assertSame('Your product file is garbage!', $comments[0]['content']);
        static::assertSame('julia@roberts.com', $comments[0]['author_email']);
        static::assertSame('2022-09-08 17:02:52', $comments[0]['created_at']);
        static::assertSame('I\'m kidding, it\'s awesome!', $comments[1]['content']);
        static::assertSame('julia@roberts.com', $comments[1]['author_email']);
        static::assertSame('2022-09-08 17:02:53', $comments[1]['created_at']);
    }

    /** @test */
    public function itSavesSupplierCommentsForAProductFile(): void
    {
        $this->createSupplier();
        $repository = $this->get(ProductFileRepository::class);
        $productFile = ProductFile::create(
            'b8b13d0b-496b-4a7c-a574-0d522ba90752',
            'product-file.xlsx',
            '1/2/3/4/product-file.xlsx',
            'jimmy@punchline.com',
            new Supplier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', 'jimmy_punchline', 'Jimmy Punchline'),
        );
        $firstCommentCreatedAt = new \DateTimeImmutable('2022-09-08 17:02:52');
        $productFile->addNewSupplierComment(
            'Here are the products I\'ve got for you.',
            'jimmy@punchline.com',
            $firstCommentCreatedAt,
        );
        $secondCommentCreatedAt = new \DateTimeImmutable('2022-09-08 17:02:53');
        $productFile->addNewSupplierComment(
            'I\'m gonna submit an other product file to you.',
            'jimmy@punchline.com',
            $secondCommentCreatedAt,
        );

        $repository->save($productFile);

        $comments = $this->findSupplierCommentsForProductFile('b8b13d0b-496b-4a7c-a574-0d522ba90752');

        static::assertCount(2, $comments);
        static::assertSame('Here are the products I\'ve got for you.', $comments[0]['content']);
        static::assertSame('jimmy@punchline.com', $comments[0]['author_email']);
        static::assertSame('2022-09-08 17:02:52', $comments[0]['created_at']);
        static::assertSame('I\'m gonna submit an other product file to you.', $comments[1]['content']);
        static::assertSame('jimmy@punchline.com', $comments[1]['author_email']);
        static::assertSame('2022-09-08 17:02:53', $comments[1]['created_at']);
    }

    private function findProductFile(string $originalFilename): ?array
    {
        $sql = <<<SQL
            SELECT *
            FROM `akeneo_supplier_portal_supplier_product_file`
            WHERE original_filename = :original_filename
        SQL;

        $productFile = $this->get(Connection::class)
            ->executeQuery($sql, ['original_filename' => $originalFilename])
            ->fetchAssociative();

        return $productFile ?: null;
    }

    private function findRetailerCommentsForProductFile(string $productFileIdentifier): array
    {
        $sql = <<<SQL
            SELECT author_email, content, created_at
            FROM akeneo_supplier_portal_product_file_retailer_comments
            WHERE product_file_identifier = :productFileIdentifier
            ORDER BY created_at;
        SQL;

        return $this->get(Connection::class)
            ->executeQuery($sql, ['productFileIdentifier' => $productFileIdentifier])
            ->fetchAllAssociative();
    }

    private function findSupplierCommentsForProductFile(string $productFileIdentifier): array
    {
        $sql = <<<SQL
            SELECT author_email, content, created_at
            FROM akeneo_supplier_portal_product_file_supplier_comments
            WHERE product_file_identifier = :productFileIdentifier
            ORDER BY created_at;
        SQL;

        return $this->get(Connection::class)
            ->executeQuery($sql, ['productFileIdentifier' => $productFileIdentifier])
            ->fetchAllAssociative();
    }

    private function createSupplier(): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_supplier_portal_supplier (identifier, code, label) 
            VALUES ('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', 'supplier-1', 'Supplier 1');
        SQL;

        $this->get(Connection::class)->executeStatement($sql);
    }
}
