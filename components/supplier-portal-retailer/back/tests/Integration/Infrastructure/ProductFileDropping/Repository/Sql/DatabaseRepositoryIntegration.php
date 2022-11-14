<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Repository\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Comment;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseRepositoryIntegration extends SqlIntegrationTestCase
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
    public function itSavesAProductFile(): void
    {
        $repository = $this->get(ProductFileRepository::class);
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('b8b13d0b-496b-4a7c-a574-0d522ba90752')
            ->uploadedBySupplier(
                new Supplier(
                    'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                    'supplier_code',
                    'Supplier label',
                ),
            )
            ->build();
        $repository->save($productFile);

        $savedProductFile = $this->findProductFile('file.xlsx');

        $this->assertSame($productFile->originalFilename(), $savedProductFile['original_filename']);
        $this->assertSame($productFile->path(), $savedProductFile['path']);
        $this->assertSame($productFile->contributorEmail(), $savedProductFile['uploaded_by_contributor']);
        $this->assertSame($productFile->uploadedBySupplier(), $savedProductFile['uploaded_by_supplier']);
        $this->assertFalse((bool) $savedProductFile['downloaded']);
    }

    /** @test */
    public function itSavesRetailerCommentsForAProductFile(): void
    {
        $repository = $this->get(ProductFileRepository::class);
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('b8b13d0b-496b-4a7c-a574-0d522ba90752')
            ->uploadedBySupplier(new Supplier(
                'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                'supplier_code',
                'Supplier label',
            ))
            ->build();
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
        $repository = $this->get(ProductFileRepository::class);
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('b8b13d0b-496b-4a7c-a574-0d522ba90752')
            ->uploadedBySupplier(new Supplier(
                'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                'supplier_code',
                'Supplier label',
            ))
            ->build();
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

    /** @test */
    public function itFindsAProductFileFromItsIdentifier(): void
    {
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('8d388bdc-8243-4e88-9c7c-6be0d7afb9df')
            ->uploadedBySupplier(new Supplier(
                'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                'supplier_code',
                'Supplier label',
            ))
            ->withContributorEmail('contributor@contributor.com')
            ->uploadedAt(new \DateTimeImmutable('2022-09-07 08:54:38'))
            ->build();
        $retailerCommentDate = new \DateTimeImmutable('2022-09-07 00:00:00');
        $productFile->addNewRetailerComment(
            'Your product file is awesome!',
            'julia@roberts.com',
            $retailerCommentDate,
        );
        $supplierCommentDate = new \DateTimeImmutable('2022-09-07 00:00:01');
        $productFile->addNewSupplierComment(
            'Here are the products I\'ve got for you.',
            'jimmy@punchline.com',
            $supplierCommentDate,
        );
        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save($productFile);

        /** @var ProductFile $productFile */
        $productFile = $productFileRepository->find(Identifier::fromString('8d388bdc-8243-4e88-9c7c-6be0d7afb9df'));

        static::assertSame('file.xlsx', $productFile->originalFilename());
        static::assertSame('path/to/file.xlsx', $productFile->path());
        static::assertSame('contributor@contributor.com', $productFile->contributorEmail());
        static::assertSame('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', $productFile->uploadedBySupplier());
        static::assertSame('2022-09-07 08:54:38', $productFile->uploadedAt());
        static::assertFalse($productFile->downloaded());
        static::assertContainsOnly(Comment::class, $productFile->retailerComments());
        static::assertContainsOnly(Comment::class, $productFile->supplierComments());
        static::assertCount(1, $productFile->retailerComments());
        static::assertCount(1, $productFile->supplierComments());

        static::assertSame('Your product file is awesome!', $productFile->retailerComments()[0]->content());
        static::assertSame('julia@roberts.com', $productFile->retailerComments()[0]->authorEmail());
        static::assertEquals($retailerCommentDate, $productFile->retailerComments()[0]->createdAt());

        static::assertSame('Here are the products I\'ve got for you.', $productFile->supplierComments()[0]->content());
        static::assertSame('jimmy@punchline.com', $productFile->supplierComments()[0]->authorEmail());
        static::assertEquals($supplierCommentDate, $productFile->supplierComments()[0]->createdAt());
    }

    /** @test */
    public function itDeletesProductFileRetailerComments(): void
    {
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('3a0c0ab8-9082-4275-8ba0-cf5921695f8b')
            ->uploadedAt(new \DateTimeImmutable('2022-09-07 08:54:38'))
            ->uploadedBySupplier(new Supplier(
                'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                'supplier_code',
                'Supplier label',
            ))
            ->build();
        $retailerCommentDate = new \DateTimeImmutable('2022-09-07 00:00:00');
        $productFile->addNewRetailerComment(
            'Your product file is awesome!',
            'julia@roberts.com',
            $retailerCommentDate,
        );
        $supplierCommentDate = new \DateTimeImmutable('2022-09-07 00:00:01');
        $productFile->addNewSupplierComment(
            'Here are the products I\'ve got for you.',
            'jimmy@punchline.com',
            $supplierCommentDate,
        );
        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save($productFile);
        $productFileRepository->deleteProductFileRetailerComments($productFile->identifier());
        $productFile = $productFileRepository->find(Identifier::fromString('3a0c0ab8-9082-4275-8ba0-cf5921695f8b'));

        static::assertCount(1, $productFile->supplierComments());
        static::assertCount(0, $productFile->retailerComments());
    }

    /** @test */
    public function itDeletesProductFileSupplierComments(): void
    {
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('24580328-66cd-4c5b-b0c9-4e83ed7af9bb')
            ->uploadedAt(new \DateTimeImmutable('2022-09-07 08:54:38'))
            ->uploadedBySupplier(new Supplier(
                'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                'supplier_code',
                'Supplier label',
            ))
            ->build();
        $retailerCommentDate = new \DateTimeImmutable('2022-09-07 00:00:00');
        $productFile->addNewRetailerComment(
            'Your product file is awesome!',
            'julia@roberts.com',
            $retailerCommentDate,
        );
        $supplierCommentDate = new \DateTimeImmutable('2022-09-07 00:00:01');
        $productFile->addNewSupplierComment(
            'Here are the products I\'ve got for you.',
            'jimmy@punchline.com',
            $supplierCommentDate,
        );
        $productFileRepository = $this->get(ProductFileRepository::class);
        $productFileRepository->save($productFile);
        $productFileRepository->deleteProductFileSupplierComments($productFile->identifier());
        $productFile = $productFileRepository->find(Identifier::fromString('24580328-66cd-4c5b-b0c9-4e83ed7af9bb'));

        static::assertCount(0, $productFile->supplierComments());
        static::assertCount(1, $productFile->retailerComments());
    }

    /** @test */
    public function itDeletesOldProductFiles(): void
    {
        $productFileRepository = $this->get(ProductFileRepository::class);
        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')
                ->build(),
        );

        $supplier = new Supplier(
            'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
            'supplier_code',
            'Supplier label',
        );

        for ($i = 0; 3 > $i; $i++) {
            $this->get(ProductFileRepository::class)->save(
                (new ProductFileBuilder())
                    ->uploadedBySupplier($supplier)
                    ->withOriginalFilename(sprintf('file%d.xlsx', $i + 1))
                    ->uploadedAt(
                        (new \DateTimeImmutable())->add(
                            \DateInterval::createFromDateString(sprintf('-%d days', ($i + 1) * 40)),
                        ),
                    )
                    ->build(),
            );
        }
        $productFileRepository->deleteOldProductFiles();
        $supplierProductFilenames = $this->get(Connection::class)
            ->executeQuery(
                <<<SQL
                SELECT original_filename
                FROM `akeneo_supplier_portal_supplier_product_file`
            SQL
            )
            ->fetchAllAssociative()
        ;

        static::assertEqualsCanonicalizing([
            ['original_filename' => 'file1.xlsx'],
            ['original_filename' => 'file2.xlsx'],
        ], $supplierProductFilenames);
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
}
