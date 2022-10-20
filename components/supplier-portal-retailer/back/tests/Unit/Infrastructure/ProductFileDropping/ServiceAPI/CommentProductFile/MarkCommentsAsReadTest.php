<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplierHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotHaveComments;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead\Exception\ProductFileDoesNotExist as ProductFileDoesNotExistServiceApi;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead\Exception\ProductFileDoesNotHaveComments as ProductFileDoesNotHaveCommentsServiceAPI;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead\MarkCommentsAsRead;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead\MarkCommentsAsReadCommand;
use PHPUnit\Framework\TestCase;

final class MarkCommentsAsReadTest extends TestCase
{
    /** @test */
    public function itMarksCommentsAsRead(): void
    {
        $commandHandler = $this->createMock(MarkCommentsAsReadBySupplierHandler::class);
        $serviceAPI = new MarkCommentsAsRead($commandHandler);

        $commandHandler
            ->expects($this->once())
            ->method('__invoke')
            ->with(new MarkCommentsAsReadBySupplier('e77c4413-a6d5-49e6-a102-8042cf5bd439', new \DateTimeImmutable('2022-10-20 02:38:45')));

        ($serviceAPI)(new MarkCommentsAsReadCommand('e77c4413-a6d5-49e6-a102-8042cf5bd439', new \DateTimeImmutable('2022-10-20 02:38:45')));
    }

    /** @test */
    public function itThrowsAServiceAPIExceptionIfProductFileDoesNotExist(): void
    {
        $commandHandler = $this->createMock(MarkCommentsAsReadBySupplierHandler::class);
        $serviceAPI = new MarkCommentsAsRead($commandHandler);

        $commandHandler
            ->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new ProductFileDoesNotExist());

        try {
            ($serviceAPI)(new MarkCommentsAsReadCommand('e77c4413-a6d5-49e6-a102-8042cf5bd439', new \DateTimeImmutable('2022-10-20 02:38:45')));
        } catch (\Exception $e) {
            $this->assertInstanceOf(ProductFileDoesNotExistServiceApi::class, $e);

            return;
        }

        $this->fail(sprintf('Expected a %s exception.', ProductFileDoesNotExistServiceApi::class));
    }

    /** @test */
    public function itThrowsAServiceAPIExceptionIfProductFileDoesNotHaveComments(): void
    {
        $commandHandler = $this->createMock(MarkCommentsAsReadBySupplierHandler::class);
        $serviceAPI = new MarkCommentsAsRead($commandHandler);

        $commandHandler
            ->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new ProductFileDoesNotHaveComments());

        try {
            ($serviceAPI)(new MarkCommentsAsReadCommand('e77c4413-a6d5-49e6-a102-8042cf5bd439', new \DateTimeImmutable('2022-10-20 02:38:45')));
        } catch (\Exception $e) {
            $this->assertInstanceOf(ProductFileDoesNotHaveCommentsServiceAPI::class, $e);

            return;
        }

        $this->fail(sprintf('Expected a %s exception.', ProductFileDoesNotHaveCommentsServiceAPI::class));
    }
}
