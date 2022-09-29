<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithComments;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

final class DatabaseGetProductFileWithCommentsIntegration extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')
                ->withCode('supplier_1')
                ->build(),
        );
    }

    /** @test */
    public function itGetsNullIfThereIsNoProductFileForTheGivenIdentifier(): void
    {
        static::assertNull($this->get(GetProductFileWithComments::class)('82a69bc5-111b-4b79-9fc1-2421e1d304e7'));
    }

    /** @test */
    public function itDoesNotGetAnyCommentIfThereIsNone(): void
    {
        $this->createProductFile('5d001a43-a42d-4083-8673-b64bb4ecd26f');
        $productFile = $this->get(GetProductFileWithComments::class)('5d001a43-a42d-4083-8673-b64bb4ecd26f');

        static::assertSame([
            'identifier' => '5d001a43-a42d-4083-8673-b64bb4ecd26f',
            'originalFilename' => 'file.xlsx',
            'path' => null,
            'uploadedByContributor' => 'jimmy@punchline.com',
            'uploadedBySupplier' => 'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
            'uploadedAt' => '2022-09-07 08:54:38',
            'retailerComments' => [],
            'supplierComments' => [],
        ], $productFile->toArray());
    }

    /** @test */
    public function itGetsAProductFileWithItsComments(): void
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
        $productFile = $this->get(GetProductFileWithComments::class)('5d001a43-a42d-4083-8673-b64bb4ecd26f');

        static::assertSame('5d001a43-a42d-4083-8673-b64bb4ecd26f', $productFile->identifier);
        static::assertSame('file.xlsx', $productFile->originalFilename);
        static::assertSame('jimmy@punchline.com', $productFile->uploadedByContributor);
        static::assertSame('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', $productFile->uploadedBySupplier);
        static::assertSame('2022-09-07 08:54:38', $productFile->uploadedAt);
        static::assertEquals([
            [
                'content' => 'Your product file is awesome!',
                'author_email' => 'julia@roberts.com',
                'created_at' => '2022-09-07 00:00:00.000000',
            ],
        ], $productFile->retailerComments);
        static::assertEquals([
            [
                'content' => 'Here are the products I\'ve got for you.',
                'author_email' => 'jimmy@punchline.com',
                'created_at' => '2022-09-07 00:00:01.000000',
            ],
        ], $productFile->supplierComments);
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
                'path' => 'path/to/file/file.xlsx',
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
