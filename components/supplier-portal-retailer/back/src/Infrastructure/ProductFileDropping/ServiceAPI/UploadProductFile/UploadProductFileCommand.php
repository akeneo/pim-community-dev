<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UploadProductFileCommand
{
    public function __construct(public UploadedFile $uploadedFile, public string $contributorEmail)
    {
    }
}
