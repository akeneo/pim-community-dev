<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write;

final class CreateProductFile
{
    public function __construct(
        public string $originalFileName,
        public string $temporaryFilePath,
        public string $uploadedByContributor,
    ) {
    }
}
