<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception;

final class InvalidComment extends \Exception
{
    private function __construct(public string $errorCode)
    {
    }

    public static function commentTooLong(): self
    {
        return new self('comment_too_long');
    }

    public static function emptyComment(): self
    {
        return new self('empty_comment');
    }

    public static function maxCommentsPerProductFileReached(): self
    {
        return new self('max_comments_limit_reached');
    }
}
