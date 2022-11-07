<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadByRetailer;

final class MarkCommentsAsReadByRetailer
{
    public function __construct(
        public string $productFileIdentifier,
        public \DateTimeImmutable $lastReadAt,
    ) {
    }
}
