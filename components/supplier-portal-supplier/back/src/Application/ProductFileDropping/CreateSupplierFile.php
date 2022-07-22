<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping;

final class CreateSupplierFile
{
    public function __construct(
        public string $filename,
        public string $temporaryPath,
        public string $uploadedByContributor,
    ) {
    }
}
