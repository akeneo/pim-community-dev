<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFilesForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ProductFilesList
{
    public function __construct(
        private ListProductFiles $getProductFiles,
        private GetProductFilesCount $getProductFilesCount,
    ) {
    }

    public function __invoke(Request $request, string $supplierIdentifier): JsonResponse
    {
        $page = $request->query->getInt('page', 1);

        $productFiles = ($this->getProductFiles)($supplierIdentifier, $page);

        return new JsonResponse([
            'product_files' => array_map(
                fn (ProductFile $productFile) => $productFile->toArray(),
                $productFiles,
            ),
            'total' => ($this->getProductFilesCount)($supplierIdentifier),
            'items_per_page' => ListProductFilesForSupplier::NUMBER_OF_PRODUCT_FILES,
        ]);
    }
}
