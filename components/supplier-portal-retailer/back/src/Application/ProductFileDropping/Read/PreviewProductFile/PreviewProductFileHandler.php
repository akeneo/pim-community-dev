<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\PreviewProductFile;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePathAndFileName;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePreview;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\ProductFileDoesNotExist;

final class PreviewProductFileHandler
{
    public function __construct(
        private readonly GetProductFilePathAndFileName $getProductFilePathAndFileName,
        private readonly GetProductFilePreview $getProductFilePreview,
    ) {
    }

    public function __invoke(PreviewProductFile $previewProductFile): array
    {
        $productFileNameAndFilePath = ($this->getProductFilePathAndFileName)($previewProductFile->productFileIdentifier);

        if (null === $productFileNameAndFilePath) {
            throw new ProductFileDoesNotExist();
        }

        $productFilePreview = ($this->getProductFilePreview)($productFileNameAndFilePath->path, $productFileNameAndFilePath->originalFilename);

        return $productFilePreview->preview;
    }
}
