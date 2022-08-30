<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetSupplierFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFilesForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class SupplierFilesList
{
    public function __construct(
        private ListProductFiles $getSupplierFiles,
        private GetSupplierFilesCount $getSupplierFilesCount,
    ) {
    }

    public function __invoke(Request $request, string $supplierIdentifier): JsonResponse
    {
        $page = $request->query->getInt('page', 1);

        $supplierFiles = ($this->getSupplierFiles)($supplierIdentifier, $page);

        return new JsonResponse([
            'supplier_files' => array_map(
                fn (ProductFile $supplierFile) => $supplierFile->toArray(),
                $supplierFiles,
            ),
            'total' => ($this->getSupplierFilesCount)($supplierIdentifier),
            'items_per_page' => ListProductFilesForSupplier::NUMBER_OF_PRODUCT_FILES,
        ]);
    }
}
