<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithMetadataAndComments;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\ProductFileImportRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileImportBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Akeneo\SupplierPortal\Retailer\Test\Unit\Fakes\FrozenClock;

final class DatabaseGetProductFileWithMetadataAndCommentsIntegration extends SqlIntegrationTestCase
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
        static::assertNull(
            $this->get(GetProductFileWithMetadataAndComments::class)('82a69bc5-111b-4b79-9fc1-2421e1d304e7'),
        );
    }

    /** @test */
    public function itDoesNotGetAnyCommentIfThereIsNone(): void
    {
        ($this->get(ProductFileRepository::class))->save(
            (new ProductFileBuilder())
                ->withIdentifier('5d001a43-a42d-4083-8673-b64bb4ecd26f')
                ->uploadedBySupplier(
                    new Supplier(
                        'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                        'supplier_1',
                        'Supplier label',
                    ),
                )
                ->withContributorEmail('contributor@contributor.com')
                ->uploadedAt(new \DateTimeImmutable('2022-09-07 08:54:38'))
                ->build(),
        );

        $productFile = $this->get(GetProductFileWithMetadataAndComments::class)('5d001a43-a42d-4083-8673-b64bb4ecd26f');

        static::assertSame([
            'identifier' => '5d001a43-a42d-4083-8673-b64bb4ecd26f',
            'originalFilename' => 'file.xlsx',
            'uploadedByContributor' => 'contributor@contributor.com',
            'uploadedBySupplier' => 'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
            'uploadedAt' => '2022-09-07 08:54:38',
            'retailerComments' => [],
            'supplierComments' => [],
            'retailerLastReadAt' => null,
            'importStatus' => ProductFileImportStatus::TO_IMPORT->value,
            'importDate' => null,
            'supplierLabel' => 'Supplier label',
        ], $productFile->toArray());
    }

    /** @test */
    public function itGetsAProductFileWithMetadataAndComments(): void
    {
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('5d001a43-a42d-4083-8673-b64bb4ecd26f')
            ->uploadedBySupplier(
                new Supplier(
                    'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                    'supplier_1',
                    'Supplier label',
                ),
            )
            ->withContributorEmail('contributor@contributor.com')
            ->uploadedAt(new \DateTimeImmutable('2022-09-07 08:54:38'))
            ->build();
        $productFileImport = (new ProductFileImportBuilder())
            ->withProductFile($productFile)
            ->importedAt((new FrozenClock('2022-09-08 08:54:38'))->now())
            ->withImportExecutionId(2)
            ->withImportStatus(ProductFileImportStatus::COMPLETED)
            ->build();
        $productFile->addNewRetailerComment(
            'Your product file is awesome!',
            'julia@roberts.com',
            new \DateTimeImmutable('2022-09-07 00:00:00'),
        );
        $productFile->addNewSupplierComment(
            'Here are the products I\'ve got for you.',
            'jimmy@punchline.com',
            new \DateTimeImmutable('2022-09-07 00:00:01'),
        );
        ($this->get(ProductFileRepository::class))->save($productFile);
        ($this->get(ProductFileImportRepository::class))->save($productFileImport);

        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_product_file_comments_read_by_retailer` (
                product_file_identifier, last_read_at                
            ) VALUES (:productFileIdentifier, :lastReadAt);
        SQL;
        $this->get(Connection::class)->executeStatement(
            $sql,
            [
                'productFileIdentifier' => '5d001a43-a42d-4083-8673-b64bb4ecd26f',
                'lastReadAt' => (new \DateTimeImmutable('2022-09-07 00:00:00'))->format('Y-m-d H:i:s'),
            ],
        );

        $productFile = $this->get(GetProductFileWithMetadataAndComments::class)('5d001a43-a42d-4083-8673-b64bb4ecd26f');

        static::assertSame('5d001a43-a42d-4083-8673-b64bb4ecd26f', $productFile->identifier);
        static::assertSame('file.xlsx', $productFile->originalFilename);
        static::assertSame('contributor@contributor.com', $productFile->uploadedByContributor);
        static::assertSame('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7', $productFile->uploadedBySupplier);
        static::assertSame('2022-09-07 08:54:38', $productFile->uploadedAt);
        static::assertSame(ProductFileImportStatus::COMPLETED->value, $productFile->importStatus);
        static::assertSame('2022-09-08 08:54:38', $productFile->importDate);
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
        static::assertSame('2022-09-07 00:00:00', $productFile->retailerLastReadAt);
    }
}
