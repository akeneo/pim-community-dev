<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CommentProductFileForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CommentProductFileHandlerForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository as ProductFileInMemoryRepository;
use PHPUnit\Framework\TestCase;

final class CommentProductFileHandlerForSupplierTest extends TestCase
{
    /** @test */
    public function itCommentsAProductFile(): void
    {
        $productFileRepository = new ProductFileInMemoryRepository();
        $productFileRepository->save(ProductFile::create(
            '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
            'file.xlsx',
            'path/to/file.xlsx',
            'contributor@example.com',
            new Supplier(
                '64e9aa37-5935-4092-bbe6-54fe271fb2a7',
                'los_pollos_hermanos',
                'Los Pollos Hermanos',
            ),
        ));
        $command = new CommentProductFileForSupplier(
            '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
            'contributor@example.com',
            'Here are the products I\'ve got for you.',
            new \DateTimeImmutable(),
        );
        $sut = new CommentProductFileHandlerForSupplier($productFileRepository);

        ($sut)($command);

        $productFile = $productFileRepository->find(
            Identifier::fromString(
                '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
            ),
        );

        static::assertInstanceOf(ProductFile::class, $productFile);
        static::assertCount(1, $productFile->newSupplierComments());
        static::assertSame('contributor@example.com', $productFile->newSupplierComments()[0]->authorEmail());
        static::assertSame('Here are the products I\'ve got for you.', $productFile->newSupplierComments()[0]->content());
    }

    /** @test */
    public function itThrowsAnExceptionIfWeTryToCommentAProductFileThatDoesNotExist(): void
    {
        static::expectExceptionObject(new ProductFileDoesNotExist());

        $productFileRepository = new ProductFileInMemoryRepository();
        $command = new CommentProductFileForSupplier(
            '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
            'contributor@example.com',
            'Here are the products I\'ve got for you.',
            new \DateTimeImmutable(),
        );
        $sut = new CommentProductFileHandlerForSupplier($productFileRepository);

        ($sut)($command);
    }
}
