<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CreateSupplierFileHandler;

final class UploadProductFile
{
    public function __construct(private CreateSupplierFileHandler $createSupplierFileHandler)
    {
    }

    public function __invoke(UploadProductFileQuery $uploadProductFileQuery)
    {

    }
}
