<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CommentProductFileForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CommentProductFileHandlerForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\CommentTooLong;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\EmptyComment;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\MaxCommentPerProductFileReached;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\CommentProductFile;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\CommentProductFileCommand;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception\CommentTooLong as CommentTooLongApiException;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception\EmptyComment as EmptyCommentApiException;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception\MaxCommentPerProductFileReached as MaxCommentPerProductFileReachedApiException;
use PHPUnit\Framework\TestCase;

final class CommentProductFileTest extends TestCase
{
    /** @test */
    public function itAddsASupplierCommentOnAProductFile(): void
    {
        $commandHandler = $this->createMock(CommentProductFileHandlerForSupplier::class);
        $sut = new CommentProductFile($commandHandler);

        $serviceApiCommand = $this->generateCommentProductFileCommand();

        $commandHandler->expects($this->once())->method('__invoke')->with(new CommentProductFileForSupplier(
            'e77c4413-a6d5-49e6-a102-8042cf5bd439',
            'jimmy@megasupplier.com',
            'Here are your products',
            $serviceApiCommand->createdAt,
        ));
        ($sut)($serviceApiCommand);
    }

    /** @test */
    public function itReturnsAServiceAPIExceptionIfADomainEmptyCommentExceptionOccured(): void
    {
        $commandHandler = $this->createMock(CommentProductFileHandlerForSupplier::class);
        $sut = new CommentProductFile($commandHandler);

        $serviceApiCommand = $this->generateCommentProductFileCommand();

        $commandHandler->expects($this->once())->method('__invoke')->willThrowException(new EmptyComment());
        $this->expectException(EmptyCommentApiException::class);
        ($sut)($serviceApiCommand);
    }

    /** @test */
    public function itReturnsAServiceAPIExceptionIfADomainCommentTooLongExceptionOccured(): void
    {
        $commandHandler = $this->createMock(CommentProductFileHandlerForSupplier::class);
        $sut = new CommentProductFile($commandHandler);

        $serviceApiCommand = $this->generateCommentProductFileCommand();

        $commandHandler->expects($this->once())->method('__invoke')->willThrowException(new CommentTooLong());
        $this->expectException(CommentTooLongApiException::class);
        ($sut)($serviceApiCommand);
    }

    /** @test */
    public function itReturnsAServiceAPIExceptionIfADomainMaxCommentPerProductFileReachedExceptionOccured(): void
    {
        $commandHandler = $this->createMock(CommentProductFileHandlerForSupplier::class);
        $sut = new CommentProductFile($commandHandler);

        $serviceApiCommand = $this->generateCommentProductFileCommand();

        $commandHandler->expects($this->once())->method('__invoke')->willThrowException(new MaxCommentPerProductFileReached());
        $this->expectException(MaxCommentPerProductFileReachedApiException::class);
        ($sut)($serviceApiCommand);
    }

    private function generateCommentProductFileCommand(): CommentProductFileCommand
    {
        return new CommentProductFileCommand(
            'e77c4413-a6d5-49e6-a102-8042cf5bd439',
            'jimmy@megasupplier.com',
            'Here are your products',
            new \DateTimeImmutable(),
        );
    }
}
