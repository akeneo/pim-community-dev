<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\CountProductFileComments;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builders\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseCountProductFileCommentsIntegration extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('cf09479f-f8c3-4128-a3f9-addbaac9362e')
                ->build(),
        );
    }

    /** @test */
    public function itReturnsZeroIfTheGivenProductFileDoesNotExist(): void
    {
        static::assertSame(
            0,
            $this->get(CountProductFileComments::class)('8a60832e-d98b-4ee0-83a8-b23d7394a7c0'),
        );
    }

    /** @test */
    public function itReturnsZeroIfThereIsNoComment(): void
    {
        $this->createProductFile('594bb1e2-72a8-4bac-8651-e4a5384f3cdf');

        $numberOfProductFileComments = $this->get(CountProductFileComments::class)(
            '594bb1e2-72a8-4bac-8651-e4a5384f3cdf'
        );

        static::assertSame(0, $numberOfProductFileComments);
    }

    /** @test */
    public function itCountsTheNumberOfCommentsForAProductFileWhenThereIsOnlyRetailerComments(): void
    {
        $this->createProductFile('594bb1e2-72a8-4bac-8651-e4a5384f3cdf');
        $this->createRetailerComment();

        $numberOfProductFileComments = $this->get(CountProductFileComments::class)(
            '594bb1e2-72a8-4bac-8651-e4a5384f3cdf'
        );

        static::assertSame(1, $numberOfProductFileComments);
    }

    /** @test */
    public function itCountsTheNumberOfCommentsForAProductFileWhenThereIsRetailerAndSupplierComments(): void
    {
        $this->createProductFile('594bb1e2-72a8-4bac-8651-e4a5384f3cdf');
        $this->createRetailerComment();
        $this->createSupplierComment();

        $numberOfProductFileComments = $this->get(CountProductFileComments::class)(
            '594bb1e2-72a8-4bac-8651-e4a5384f3cdf'
        );

        static::assertSame(2, $numberOfProductFileComments);
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
                'supplierIdentifier' => 'cf09479f-f8c3-4128-a3f9-addbaac9362e',
                'uploadedAt' => '2022-09-07 08:54:38',
            ],
        );
    }

    private function createRetailerComment(): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_product_file_retailer_comments` (
                author_email,
                product_file_identifier,
                content,
                created_at
            )
            VALUES (:authorEmail, :productFileIdentifier, :content, :createdAt)
        SQL;

        $this->get(Connection::class)->executeStatement(
            $sql,
            [
                'authorEmail' => 'julia@roberts.com',
                'productFileIdentifier' => '594bb1e2-72a8-4bac-8651-e4a5384f3cdf',
                'content' => 'Your product file is awesome!',
                'createdAt' => '2022-09-09 08:41:43',
            ],
        );
    }

    private function createSupplierComment(): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_product_file_supplier_comments` (
                author_email,
                product_file_identifier,
                content,
                created_at
            )
            VALUES (:authorEmail, :productFileIdentifier, :content, :createdAt)
        SQL;

        $this->get(Connection::class)->executeStatement(
            $sql,
            [
                'authorEmail' => 'jimmy.punchline@los_pollos_hermanos.com',
                'productFileIdentifier' => '594bb1e2-72a8-4bac-8651-e4a5384f3cdf',
                'content' => 'Here are the products I\'ve got for you.',
                'createdAt' => '2022-09-09 08:43:43',
            ],
        );
    }
}
