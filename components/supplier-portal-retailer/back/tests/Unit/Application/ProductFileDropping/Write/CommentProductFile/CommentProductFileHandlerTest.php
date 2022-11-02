<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping\Write\CommentProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFile\CommentProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFile\CommentProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository as ProductFileInMemoryRepository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use PHPUnit\Framework\TestCase;

final class CommentProductFileHandlerTest extends TestCase
{
    /** @test */
    public function itCommentsAProductFile(): void
    {
        $productFileRepository = new ProductFileInMemoryRepository();
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withIdentifier('6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287')
                ->build(),
        );
        $command = new CommentProductFile(
            '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
            'julia@roberts.com',
            'Your product file is awesome!',
            new \DateTimeImmutable(),
        );

        (new CommentProductFileHandler($productFileRepository))($command);

        $productFile = $productFileRepository->find(
            Identifier::fromString(
                '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
            ),
        );

        static::assertInstanceOf(ProductFile::class, $productFile);
        static::assertCount(1, $productFile->newRetailerComments());
        static::assertSame('julia@roberts.com', $productFile->newRetailerComments()[0]->authorEmail());
        static::assertSame('Your product file is awesome!', $productFile->newRetailerComments()[0]->content());
    }

    /** @test */
    public function itThrowsAnExceptionIfWeTryToCommentAProductFileThatDoesNotExist(): void
    {
        static::expectExceptionObject(new ProductFileDoesNotExist());

        $productFileRepository = new ProductFileInMemoryRepository();
        $command = new CommentProductFile(
            '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
            'julia@roberts.com',
            'Your product file is awesome!',
            new \DateTimeImmutable(),
        );

        (new CommentProductFileHandler($productFileRepository))($command);
    }
}
