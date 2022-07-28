<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\GetAllSupplierFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\GetSupplierFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\SupplierFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class SupplierFilesList
{
    public function __construct(private GetAllSupplierFiles $getSupplierFiles, private GetSupplierFilesCount $getSupplierFilesCount)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $supplierFiles = ($this->getSupplierFiles)($page);

        return new JsonResponse([
            'supplier_files' => array_map(
                fn (SupplierFile $supplierFile) => $supplierFile->toArray(),
                $supplierFiles,
            ),
            'total' => ($this->getSupplierFilesCount)(),
            'items_per_page' => GetAllSupplierFiles::NUMBER_OF_SUPPLIER_FILES_PER_PAGE,
        ]);
    }
}
