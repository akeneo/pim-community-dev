<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping\Write;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplier as MarkCommentsAsReadBySupplierCommand;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplierHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\MarkCommentsAsReadBySupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository as ProductFileInMemoryRepository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use PHPUnit\Framework\TestCase;

final class MarkCommentsAsReadBySupplierHandlerTest extends TestCase
{
    /** @test */
    public function itThrowsAnExceptionIfWeTryToMarkAsReadCommentsOfAProductFileThatDoesNotExist(): void
    {
        static::expectExceptionObject(new ProductFileDoesNotExist());

        $productFileRepository = new ProductFileInMemoryRepository();

        $queryMock = $this->createMock(MarkCommentsAsReadBySupplier::class);

        (new MarkCommentsAsReadBySupplierHandler($queryMock, $productFileRepository))(
            new MarkCommentsAsReadBySupplierCommand(
                '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
                new \DateTimeImmutable('2022-10-19 05:25:48'),
            )
        );
    }

    /** @test */
    public function itMarksProductFileCommentsAsReadForASupplier(): void
    {
        $productFileRepository = new ProductFileInMemoryRepository();
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287')
            ->build();
        $productFile->addNewRetailerComment('content', 'julia@roberts.com', new \DateTimeImmutable('2022-09-07 00:00:00'));
        $productFileRepository->save($productFile);

        $lastReadAt = new \DateTimeImmutable('2022-10-19 05:25:48');

        $queryMock = $this->createMock(MarkCommentsAsReadBySupplier::class);
        $queryMock->expects($this->once())->method('__invoke')->with('6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287', $lastReadAt);

        (new MarkCommentsAsReadBySupplierHandler($queryMock, $productFileRepository))(
            new MarkCommentsAsReadBySupplierCommand(
                '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
                $lastReadAt,
            )
        );
    }

    /** @test */
    public function itDoesNothingIfWeTryToMarkAsReadCommentsOfAProductFileThatDoesNotHaveAnyComment(): void
    {
        $productFileRepository = new ProductFileInMemoryRepository();
        $productFileRepository->save(
            (new ProductFileBuilder())
                ->withIdentifier('6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287')
                ->build(),
        );

        $lastReadAt = new \DateTimeImmutable('2022-10-19 05:25:48');

        $queryMock = $this->createMock(MarkCommentsAsReadBySupplier::class);
        $queryMock->expects($this->never())->method('__invoke');

        (new MarkCommentsAsReadBySupplierHandler($queryMock, $productFileRepository))(
            new MarkCommentsAsReadBySupplierCommand(
                '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
                $lastReadAt,
            )
        );
    }
}
