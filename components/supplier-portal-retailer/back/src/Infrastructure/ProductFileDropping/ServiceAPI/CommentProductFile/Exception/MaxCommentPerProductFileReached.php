<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception;

final class MaxCommentPerProductFileReached extends \InvalidArgumentException
{
    public function getErrorCode(): string
    {
        return 'max_comments_limit_reached';
    }
}
