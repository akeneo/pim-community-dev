<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Event;

final class ProductFileDownloaded
{
    public function __construct(
        public string $productFileIdentifier,
        public int $userId,
    ) {
    }
}
