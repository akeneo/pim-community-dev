<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFileForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFileHandlerForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\CommentTooLong;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\EmptyComment;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\MaxCommentPerProductFileReached;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\CommentProductFile;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\CommentProductFileCommand;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception\InvalidComment;
use PHPUnit\Framework\TestCase;

final class CommentProductFileTest extends TestCase
{
    /** @test */
    public function itAddsASupplierCommentOnAProductFile(): void
    {
        $commandHandler = $this->createMock(CommentProductFileHandlerForSupplier::class);
        $sut = new CommentProductFile($commandHandler);

        $serviceApiCommand = $this->generateCommentProductFileCommand();

        $commandHandler
            ->expects($this->once())
            ->method('__invoke')
            ->with(new CommentProductFileForSupplier(
                'e77c4413-a6d5-49e6-a102-8042cf5bd439',
                'jimmy@megasupplier.com',
                'Here are your products',
                $serviceApiCommand->createdAt,
            ));

        ($sut)($serviceApiCommand);
    }

    /** @test */
    public function itReturnsAServiceAPIExceptionIfADomainEmptyCommentExceptionOccurred(): void
    {
        $commandHandler = $this->createMock(CommentProductFileHandlerForSupplier::class);
        $sut = new CommentProductFile($commandHandler);

        $commandHandler
            ->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new EmptyComment());

        try {
            ($sut)($this->generateCommentProductFileCommand());
        } catch (\Exception $e) {
            $this->assertSame(InvalidComment::class, \get_class($e));
            /** @var InvalidComment $e */
            $this->assertSame('empty_comment', $e->errorCode);

            return;
        }

        $this->fail(sprintf('Expected a %s exception with code "empty_comment".', InvalidComment::class));
    }

    /** @test */
    public function itReturnsAServiceAPIExceptionIfADomainCommentTooLongExceptionOccurred(): void
    {
        $commandHandler = $this->createMock(CommentProductFileHandlerForSupplier::class);
        $sut = new CommentProductFile($commandHandler);

        $commandHandler
            ->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new CommentTooLong());

        try {
            ($sut)($this->generateCommentProductFileCommand());
        } catch (\Exception $e) {
            $this->assertSame(InvalidComment::class, \get_class($e));
            /** @var InvalidComment $e */
            $this->assertSame('comment_too_long', $e->errorCode);

            return;
        }

        $this->fail(sprintf('Expected a %s exception with code "comment_too_long".', InvalidComment::class));
    }

    /** @test */
    public function itReturnsAServiceAPIExceptionIfADomainMaxCommentPerProductFileReachedExceptionOccurred(): void
    {
        $commandHandler = $this->createMock(CommentProductFileHandlerForSupplier::class);
        $sut = new CommentProductFile($commandHandler);

        $commandHandler
            ->expects($this->once())
            ->method('__invoke')
            ->willThrowException(new MaxCommentPerProductFileReached());

        try {
            ($sut)($this->generateCommentProductFileCommand());
        } catch (\Exception $e) {
            $this->assertSame(InvalidComment::class, \get_class($e));
            /** @var InvalidComment $e */
            $this->assertSame('max_comments_limit_reached', $e->errorCode);

            return;
        }

        $this->fail(sprintf('Expected a %s exception with code "max_comments_limit_reached".', InvalidComment::class));
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
