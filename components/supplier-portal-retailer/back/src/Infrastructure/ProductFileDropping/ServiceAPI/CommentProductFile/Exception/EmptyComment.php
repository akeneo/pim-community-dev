<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception;

final class EmptyComment extends \InvalidArgumentException
{
    public function getErrorCode(): string
    {
        return 'empty_comment';
    }
}
