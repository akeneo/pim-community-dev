<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead;

final class MarkCommentsAsReadCommand
{
    public function __construct(
        public string $productFileIdentifier,
        public \DateTimeInterface $lastReadAt,
    ) {
    }
}
