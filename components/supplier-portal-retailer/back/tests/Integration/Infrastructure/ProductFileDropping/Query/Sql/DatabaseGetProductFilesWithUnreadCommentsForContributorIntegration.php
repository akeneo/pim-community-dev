<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilesWithUnreadCommentsForContributor;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseGetProductFilesWithUnreadCommentsForContributorIntegration extends SqlIntegrationTestCase
{
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        ($this->get(Repository::class))->save(
            (new SupplierBuilder())
                ->withIdentifier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')
                ->withCode('supplier_1')
                ->withContributors(['contributor1@example.com', 'contributor2@example.com'])
                ->build(),
        );

        $this->supplier = new Supplier(
            'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
            'supplier_1',
            'Supplier label',
        );
    }

    /** @test */
    public function itGetsNothingIfThereIsNoProductFilesForTheGivenContributor(): void
    {
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('5d001a43-a42d-4083-8673-b64bb4ecd26f')
            ->uploadedBySupplier($this->supplier)
            ->withContributorEmail('contributor1@example.com')
            ->uploadedAt(new \DateTimeImmutable('2022-09-07 08:54:38'))
            ->build();

        ($this->get(ProductFileRepository::class))->save($productFile);

        static::assertEmpty($this->get(GetProductFilesWithUnreadCommentsForContributor::class)('contributor2@example.com', new \DateTimeImmutable('2022-09-07 16:54:38')));
    }

    /** @test */
    public function itGetsNothingIfThereIsNoNewCommentsForTheGivenContributor(): void
    {
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('5d001a43-a42d-4083-8673-b64bb4ecd26f')
            ->uploadedBySupplier($this->supplier)
            ->withContributorEmail('contributor1@example.com')
            ->uploadedAt(new \DateTimeImmutable('2022-09-07 08:54:38'))
            ->build();

        ($this->get(ProductFileRepository::class))->save($productFile);

        static::assertEmpty($this->get(GetProductFilesWithUnreadCommentsForContributor::class)('contributor1@example.com', new \DateTimeImmutable('2022-09-07 16:54:38')));
    }

    /** @test */
    public function itGetsProductFilesAndTheirsUnreadCommentsOfTheLast24hUploadedByAGivenContributor(): void
    {
        $currentDate = new \DateTimeImmutable('2022-09-05 08:25:35');

        $productFile = (new ProductFileBuilder())
            ->withIdentifier('6b827a50-6cd7-11ed-a1eb-0242ac120002')
            ->uploadedBySupplier($this->supplier)
            ->withContributorEmail('contributor1@example.com')
            ->uploadedAt(new \DateTimeImmutable('2022-09-01 08:54:38'))
            ->build();
        $productFile->addNewRetailerComment(
            'I\'ve got a question for you',
            'julia@roberts.com',
            $currentDate->sub(new \DateInterval('P3D')),
        );
        $productFile->addNewRetailerComment(
            'What do you call a witch who lives in the desert ?',
            'julia@roberts.com',
            $currentDate->sub(new \DateInterval('P1DT5H')),
        );
        $productFile->addNewRetailerComment(
            'A sand witch',
            'julia@roberts.com',
            $currentDate->sub(new \DateInterval('PT5H')),
        );

        ($this->get(ProductFileRepository::class))->save($productFile);

        $sql = <<<SQL
            INSERT INTO `akeneo_supplier_portal_product_file_comments_read_by_supplier` (
                product_file_identifier, last_read_at                
            ) VALUES (:productFileIdentifier, :lastReadAt);
        SQL;

        $this->get(Connection::class)->executeStatement(
            $sql,
            [
                'productFileIdentifier' => '6b827a50-6cd7-11ed-a1eb-0242ac120002',
                'lastReadAt' => $currentDate->sub(new \DateInterval('P2D'))->format('Y-m-d H:i:s'),
            ],
        );

        $productFiles = $this->get(GetProductFilesWithUnreadCommentsForContributor::class)('contributor1@example.com', $currentDate);

        static::assertCount(1, $productFiles);
        static::assertSame('6b827a50-6cd7-11ed-a1eb-0242ac120002', $productFiles[0]->identifier);
        static::assertCount(1, $productFiles[0]->retailerComments);
        static::assertEquals([[
            'content' => 'A sand witch' ,
            'author_email' => 'julia@roberts.com',
        ]], $productFiles[0]->retailerComments);
    }
}
