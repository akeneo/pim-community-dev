<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CommentProductFileForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CommentProductFileHandlerForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\CommentTooLong;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\EmptyComment;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\MaxCommentPerProductFileReached;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception\CommentTooLong as CommentTooLongApiException;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception\EmptyComment as EmptyCommentApiException;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception\MaxCommentPerProductFileReached as MaxCommentPerProductFileReachedApiException;

final class CommentProductFile
{
    public function __construct(private CommentProductFileHandlerForSupplier $commentProductFileHandlerForSupplier)
    {
    }

    public function __invoke(CommentProductFileCommand $commentProductFileCommand): void
    {
        try {
            ($this->commentProductFileHandlerForSupplier)(
                new CommentProductFileForSupplier(
                    $commentProductFileCommand->productFileIdentifier,
                    $commentProductFileCommand->authorEmail,
                    $commentProductFileCommand->content,
                    $commentProductFileCommand->createdAt,
                )
            );
        } catch (EmptyComment) {
            throw new EmptyCommentApiException();
        } catch (CommentTooLong) {
            throw new CommentTooLongApiException();
        } catch (MaxCommentPerProductFileReached) {
            throw new MaxCommentPerProductFileReachedApiException();
        }
    }
}
