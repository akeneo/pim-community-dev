<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model;

final class ProductFileWithUnreadComments
{
    public function __construct(
        public readonly string $identifier,
        public readonly string $originalFilename,
        public readonly ?string $path,
        public readonly array $retailerComments,
    ) {
    }
}
