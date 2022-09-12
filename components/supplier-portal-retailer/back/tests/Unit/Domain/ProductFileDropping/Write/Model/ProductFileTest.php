<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileAdded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use PHPUnit\Framework\TestCase;

final class ProductFileTest extends TestCase
{
    /** @test */
    public function itCreatesAProductFileAndStoresAProductFileAddedEvent(): void
    {
        $productFileIdentifier = Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2');
        $productFile = ProductFile::create(
            (string) $productFileIdentifier,
            'supplier-file.xlsx',
            '2/f/a/4/2fa4afe5465afe5655/supplier-file.xlsx',
            'contributor@example.com',
            new Supplier(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'los_pollos_hermanos',
                'Los Pollos Hermanos',
            ),
        );
        $this->assertEquals((string) $productFileIdentifier, $productFile->identifier());
        $this->assertEquals('supplier-file.xlsx', $productFile->originalFilename());
        $this->assertEquals('2/f/a/4/2fa4afe5465afe5655/supplier-file.xlsx', $productFile->path());
        $this->assertEquals('contributor@example.com', $productFile->contributorEmail());
        $this->assertIsString($productFile->uploadedAt());
        $this->assertFalse($productFile->downloaded());

        $productFileEvents = $productFile->events();
        $this->assertCount(1, $productFileEvents);
        $this->assertInstanceOf(ProductFileAdded::class, $productFileEvents[0]);
    }

    /** @test */
    public function itAddsRetailerComments(): void
    {
        $productFileIdentifier = Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2');
        $productFile = ProductFile::create(
            (string) $productFileIdentifier,
            'supplier-file.xlsx',
            '2/f/a/4/2fa4afe5465afe5655/supplier-file.xlsx',
            'jimmy@punchline.com',
            new Supplier(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'jimmy_punchline',
                'Jimmy Punchline',
            ),
        );

        $firstCommentCreatedAt = new \DateTimeImmutable();
        $productFile->addNewRetailerComment(
            'Your product file is garbage!',
            'julia@roberts.com',
            $firstCommentCreatedAt,
        );
        $secondCommentCreatedAt = (new \DateTimeImmutable())
            ->add(\DateInterval::createFromDateString('+1 second'))
        ;
        $productFile->addNewRetailerComment(
            'I\'m kidding, it\'s awesome!',
            'julia@roberts.com',
            $secondCommentCreatedAt,
        );

        static::assertCount(2, $productFile->newRetailerComments());
        static::assertSame('Your product file is garbage!', $productFile->newRetailerComments()[0]->content());
        static::assertSame('julia@roberts.com', $productFile->newRetailerComments()[0]->authorEmail());
        static::assertSame($firstCommentCreatedAt, $productFile->newRetailerComments()[0]->createdAt());
        static::assertSame('I\'m kidding, it\'s awesome!', $productFile->newRetailerComments()[1]->content());
        static::assertSame('julia@roberts.com', $productFile->newRetailerComments()[1]->authorEmail());
        static::assertSame($secondCommentCreatedAt, $productFile->newRetailerComments()[1]->createdAt());
    }

    /** @test */
    public function itAddsSupplierComments(): void
    {
        $productFileIdentifier = Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2');
        $productFile = ProductFile::create(
            (string) $productFileIdentifier,
            'supplier-file.xlsx',
            '2/f/a/4/2fa4afe5465afe5655/supplier-file.xlsx',
            'jimmy@punchline.com',
            new Supplier(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'jimmy_punchline',
                'Jimmy Punchline',
            ),
        );

        $firstCommentCreatedAt = new \DateTimeImmutable();
        $productFile->addNewSupplierComment(
            'Here are the products I\'ve got for you.',
            'jimmy@punchline.com',
            $firstCommentCreatedAt,
        );
        $secondCommentCreatedAt = (new \DateTimeImmutable())
            ->add(\DateInterval::createFromDateString('+1 second'))
        ;
        $productFile->addNewSupplierComment(
            'I\'m gonna submit an other product file to you.',
            'jimmy@punchline.com',
            $secondCommentCreatedAt,
        );

        static::assertCount(2, $productFile->newSupplierComments());
        static::assertSame(
            'Here are the products I\'ve got for you.',
            $productFile->newSupplierComments()[0]->content(),
        );
        static::assertSame('jimmy@punchline.com', $productFile->newSupplierComments()[0]->authorEmail());
        static::assertSame($firstCommentCreatedAt, $productFile->newSupplierComments()[0]->createdAt());
        static::assertSame(
            'I\'m gonna submit an other product file to you.',
            $productFile->newSupplierComments()[1]->content(),
        );
        static::assertSame('jimmy@punchline.com', $productFile->newSupplierComments()[1]->authorEmail());
        static::assertSame($secondCommentCreatedAt, $productFile->newSupplierComments()[1]->createdAt());
    }

    /** @test */
    public function itHydratesAProductFile(): void
    {
        $productFile = ProductFile::hydrate(
            'd06c58da-4cd7-469d-a3fc-37209a05e9e2',
            'supplier-file.xlsx',
            '2/f/a/4/2fa4afe5465afe5655/supplier-file.xlsx',
            'jimmy.punchline@los-pollos-hermanos.com',
            '44ce8069-8da1-4986-872f-311737f46f02',
            '2022-09-08 16:13:52',
            false,
        );

        static::assertSame('d06c58da-4cd7-469d-a3fc-37209a05e9e2', $productFile->identifier());
        static::assertSame('supplier-file.xlsx', $productFile->originalFilename());
        static::assertSame('2/f/a/4/2fa4afe5465afe5655/supplier-file.xlsx', $productFile->path());
        static::assertSame('jimmy.punchline@los-pollos-hermanos.com', $productFile->contributorEmail());
        static::assertSame('2022-09-08 16:13:52', $productFile->uploadedAt());
        static::assertFalse($productFile->downloaded());
    }
}
