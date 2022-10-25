<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping\Write;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadByRetailer;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadByRetailerHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\MarkCommentsAsReadByRetailer as MarkCommentsAsReadByRetailerQuery;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use PHPUnit\Framework\TestCase;

final class MarkCommentsAsReadByRetailerHandlerTest extends TestCase
{
    /** @test */
    public function itThrowsAnExceptionIfWeTryToMarkAsReadCommentsOfAProductFileThatDoesNotExist(): void
    {
        static::expectExceptionObject(new ProductFileDoesNotExist());

        $productFileRepository = new InMemoryRepository();

        $queryMock = $this->createMock(MarkCommentsAsReadByRetailerQuery::class);

        (new MarkCommentsAsReadByRetailerHandler($queryMock, $productFileRepository))(
            new MarkCommentsAsReadByRetailer(
                '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
                new \DateTimeImmutable('2022-10-19 05:25:48'),
            )
        );
    }

    /** @test */
    public function itMarksProductFileCommentsAsReadForARetailer(): void
    {
        $productFileRepository = new InMemoryRepository();
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287')
            ->build();
        $productFile->addNewRetailerComment('content', 'julia@roberts.com', new \DateTimeImmutable('2022-09-07 00:00:00'));
        $productFileRepository->save($productFile);

        $lastReadAt = new \DateTimeImmutable('2022-10-19 05:25:48');

        $queryMock = $this->createMock(MarkCommentsAsReadByRetailerQuery::class);
        $queryMock
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
                $lastReadAt,
            );

        (new MarkCommentsAsReadByRetailerHandler($queryMock, $productFileRepository))(
            new MarkCommentsAsReadByRetailer(
                '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
                $lastReadAt,
            )
        );
    }

    /** @test */
    public function itDoesNotTryToMarkAsReadCommentsOfAProductFileThatDoesNotHaveAnyComment(): void
    {
        $productFileRepository = new InMemoryRepository();
        $productFile = (new ProductFileBuilder())
            ->withIdentifier('6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287')
            ->build();
        $productFileRepository->save($productFile);

        $lastReadAt = new \DateTimeImmutable('2022-10-19 05:25:48');

        $queryMock = $this->createMock(MarkCommentsAsReadByRetailerQuery::class);
        $queryMock
            ->expects($this->never())
            ->method('__invoke');

        (new MarkCommentsAsReadByRetailerHandler($queryMock, $productFileRepository))(
            new MarkCommentsAsReadByRetailer(
                '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
                $lastReadAt,
            )
        );
    }
}
