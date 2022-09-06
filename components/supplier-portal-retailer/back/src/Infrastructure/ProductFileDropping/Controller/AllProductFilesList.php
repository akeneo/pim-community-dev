<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetAllProductFiles;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetAllProductFilesCount;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class AllProductFilesList
{
    public function __construct(
        private GetAllProductFiles $getProductFiles,
        private GetAllProductFilesCount $getProductFilesCount,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $productFiles = ($this->getProductFiles)($page);

        return new JsonResponse([
            'product_files' => array_map(
                fn (ProductFile $productFile) => $productFile->toArray(),
                $productFiles,
            ),
            'total' => ($this->getProductFilesCount)(),
            'items_per_page' => GetAllProductFiles::NUMBER_OF_PRODUCT_FILES_PER_PAGE,
        ]);
    }
}
