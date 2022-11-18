<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\ProductFileDropping\Query\Sql;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql\DatabaseComputeCommentsReadDelay;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Akeneo\SupplierPortal\Retailer\Test\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;

final class DatabaseComputeCommentsReadDelayIntegration extends SqlIntegrationTestCase
{
    private readonly \DateTimeImmutable $currentDateTime;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->currentDateTime = new \DateTimeImmutable();
    }

    protected function setUp(): void
    {
        parent::setUp();

        ($this->get(Repository::class))->save(
            (new SupplierBuilder())->withIdentifier('ebdbd3f4-e7f8-4790-ab62-889ebd509ae7')->build(),
        );

        $productFile = (new ProductFileBuilder())
            ->withIdentifier('6d0c2d99-731e-4bf8-a0a9-2501afc03bb8')
            ->uploadedBySupplier(
                new Supplier(
                    'ebdbd3f4-e7f8-4790-ab62-889ebd509ae7',
                    'supplier_code',
                    'Supplier label',
                ),
            )
            ->build();

        $productFile->addNewRetailerComment('comment1', 'julia@roberts.com', $this->currentDateTime->sub(new \DateInterval('P6D')));
        $productFile->addNewRetailerComment('comment2', 'julia@roberts.com', $this->currentDateTime->sub(new \DateInterval('P4D')));
        $productFile->addNewRetailerComment('comment3', 'julia@roberts.com', $this->currentDateTime->sub(new \DateInterval('P2D')));
        $this->get(ProductFileRepository::class)->save($productFile);
    }

    /** @test */
    public function itComputesCommentsReadDelaySinceLastReading(): void
    {
        $sql = <<<SQL
            INSERT INTO akeneo_supplier_portal_product_file_comments_read_by_supplier (product_file_identifier, last_read_at)
            VALUES ('6d0c2d99-731e-4bf8-a0a9-2501afc03bb8', :date);
        SQL;

        $this->get(Connection::class)->executeStatement($sql, [
            'date' => (new \DateTimeImmutable())->sub(new \DateInterval('P5D'))->format('Y-m-d H:i:s'),
        ]);

        $result = ($this->get(DatabaseComputeCommentsReadDelay::class))($this->currentDateTime, '6d0c2d99-731e-4bf8-a0a9-2501afc03bb8');

        $secondCommentReadDelayInSeconds = 60*60*24*4; //4 full days in seconds
        $thirdCommentReadDelayInSeconds = 60*60*24*2; //2 full days in seconds

        $this->assertEquals(
            [

                $secondCommentReadDelayInSeconds,
                $thirdCommentReadDelayInSeconds,
            ],
            $result,
        );
    }

    /** @test */
    public function itComputesCommentsReadDelayForTheFirstTime(): void
    {
        $result = ($this->get(DatabaseComputeCommentsReadDelay::class))($this->currentDateTime, '6d0c2d99-731e-4bf8-a0a9-2501afc03bb8');

        $firstCommentReadDelayInSeconds = 60*60*24*6; //6 full days in seconds
        $secondCommentReadDelayInSeconds = 60*60*24*4; //4 full days in seconds
        $thirdCommentReadDelayInSeconds = 60*60*24*2; //2 full days in seconds

        $this->assertEquals(
            [
                $firstCommentReadDelayInSeconds,
                $secondCommentReadDelayInSeconds,
                $thirdCommentReadDelayInSeconds,
            ],
            $result,
        );
    }
}
