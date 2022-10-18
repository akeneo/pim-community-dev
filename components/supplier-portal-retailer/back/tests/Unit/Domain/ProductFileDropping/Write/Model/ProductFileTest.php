<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileAdded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\MaxCommentPerProductFileReached;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Comment;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use PHPUnit\Framework\TestCase;

final class ProductFileTest extends TestCase
{
    /** @test */
    public function itCreatesAProductFileAndStoresAProductFileAddedEvent(): void
    {
        $productFileIdentifier = Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2');
        $productFile = (new ProductFileBuilder())
            ->withIdentifier((string) $productFileIdentifier)
            ->withOriginalFilename('file.xlsx')
            ->withPath('path/to/file.xlsx')
            ->withContributorEmail('contributor@example.com')
            ->build();
        $this->assertEquals((string) $productFileIdentifier, $productFile->identifier());
        $this->assertEquals('file.xlsx', $productFile->originalFilename());
        $this->assertEquals('path/to/file.xlsx', $productFile->path());
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
        $productFile = $this->buildProductFile(Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2'));

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
    public function itAddsRetailerCommentsOnTopOfExistingComments(): void
    {
        $productFile = $this->buildProductFile(
            Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2'),
            [Comment::hydrate('Your product file is garbage!', 'julia@roberts.com', new \DateTimeImmutable())],
            [],
        );
        $secondCommentCreatedAt = (new \DateTimeImmutable())
            ->add(\DateInterval::createFromDateString('+1 second'))
        ;
        $productFile->addNewRetailerComment(
            'I\'m kidding, it\'s awesome!',
            'julia@roberts.com',
            $secondCommentCreatedAt,
        );

        static::assertCount(2, $productFile->retailerComments());
        static::assertCount(1, $productFile->newRetailerComments());
        static::assertSame('I\'m kidding, it\'s awesome!', $productFile->newRetailerComments()[1]->content());
        static::assertSame('julia@roberts.com', $productFile->newRetailerComments()[1]->authorEmail());
        static::assertSame($secondCommentCreatedAt, $productFile->newRetailerComments()[1]->createdAt());
    }

    /** @test */
    public function itAddsSupplierComments(): void
    {
        $productFile = $this->buildProductFile(Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2'));

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
    public function itAddsSupplierCommentsOnTopOfExistingComments(): void
    {
        $productFile = $this->buildProductFile(
            Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2'),
            [],
            [Comment::hydrate('Here are the products I\'ve got for you.', 'jimmy@punchline.com', new \DateTimeImmutable())],
        );
        $secondCommentCreatedAt = (new \DateTimeImmutable())
            ->add(\DateInterval::createFromDateString('+1 second'))
        ;
        $productFile->addNewSupplierComment(
            'I\'m gonna submit an other product file to you.',
            'jimmy@punchline.com',
            $secondCommentCreatedAt,
        );

        static::assertCount(2, $productFile->supplierComments());
        static::assertCount(1, $productFile->newSupplierComments());
        static::assertSame('I\'m gonna submit an other product file to you.', $productFile->newSupplierComments()[1]->content());
        static::assertSame('jimmy@punchline.com', $productFile->newSupplierComments()[1]->authorEmail());
        static::assertSame($secondCommentCreatedAt, $productFile->newSupplierComments()[1]->createdAt());
    }

    /** @test */
    public function itThrowsAnErrorIfWeReachTheMaxCommentsLimit(): void
    {
        $comments = [];
        for ($i = 0; 50 > $i; $i++) {
            $comments[] = Comment::hydrate('Your product file is garbage!', 'julia@roberts.com', new \DateTimeImmutable());
        }
        $productFile = $this->buildProductFile(
            Identifier::fromString('d06c58da-4cd7-469d-a3fc-37209a05e9e2'),
            $comments,
            [],
        );

        $this->expectException(MaxCommentPerProductFileReached::class);

        $productFile->addNewSupplierComment(
            'Comment that throws an error',
            'julia@roberts.com',
            new \DateTimeImmutable(),
        );
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

    private function buildProductFile(Identifier $productFileIdentifier, array $retailerComments = [], array $supplierComments = []): ProductFile
    {
        return ProductFile::hydrate(
            (string) $productFileIdentifier,
            'supplier-file.xlsx',
            '2/f/a/4/2fa4afe5465afe5655/supplier-file.xlsx',
            'jimmy@punchline.com',
            '44ce8069-8da1-4986-872f-311737f46f02',
            '2022-09-29 12:00:00',
            true,
            $retailerComments,
            $supplierComments,
        );
    }
}
