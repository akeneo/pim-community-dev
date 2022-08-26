<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CreateSupplierFile
{
    public function __construct(
        public UploadedFile $uploadedFile,
        public string $originalFilename,
        public string $uploadedByContributor,
    ) {
    }
}
