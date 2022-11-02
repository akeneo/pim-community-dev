<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplier;

final class MarkCommentsAsReadBySupplier
{
    public function __construct(
        public string $productFileIdentifier,
        public \DateTimeImmutable $lastReadAt,
    ) {
    }
}
