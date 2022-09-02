<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileNameAndResourceFile;

final class ProductFile
{
    //@phpstan-ignore-next-line
    private function __construct(public string $originalFilename, public $file)
    {
    }

    public static function fromReadModel(ProductFileNameAndResourceFile $productFileNameAndResourceFile): self
    {
        return new self($productFileNameAndResourceFile->originalFilename, $productFileNameAndResourceFile->file);
    }
}
