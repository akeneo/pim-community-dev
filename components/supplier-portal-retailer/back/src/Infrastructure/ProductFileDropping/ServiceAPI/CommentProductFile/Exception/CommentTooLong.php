<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception;

final class CommentTooLong extends \InvalidArgumentException
{
    public function getErrorCode(): string
    {
        return 'comment_too_long';
    }
}
