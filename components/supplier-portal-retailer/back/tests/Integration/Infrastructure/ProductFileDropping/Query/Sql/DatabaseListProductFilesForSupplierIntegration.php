<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFilesForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

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
        $productFileRepository = $this->get(ProductFileRepository::class);
        for ($i = 0; 30 > $i; $i++) {
            $productFileRepository->save(
                (new ProductFileBuilder())
                    ->withUploadedBySupplier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')
                    ->withContributorEmail($i % 2 ? 'contributor1@example.com' : 'contributor2@example.com')
                    ->withOriginalFilename(sprintf('products_%d.xlsx', $i+1))
                    ->withUploadedAt(
                        (new \DateTimeImmutable())->add(
                            \DateInterval::createFromDateString(sprintf('%d minutes', 30 - $i)),
                        ),
                    )
                    ->build(),
            );
        }

        $supplierProductFiles = ($this->get(ListProductFilesForSupplier::class))('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7');

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
        ($this->get(ProductFileRepository::class))->save(
            (new ProductFileBuilder())
                ->withIdentifier('5d001a43-a42d-4083-8673-b64bb4ecd26f')
                ->withUploadedBySupplier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')
                ->withUploadedAt(new \DateTimeImmutable('2022-09-07 08:54:38'))
                ->build(),
        );

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
        static::assertSame('contributor@example.com', $productFiles[0]->uploadedByContributor);
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
