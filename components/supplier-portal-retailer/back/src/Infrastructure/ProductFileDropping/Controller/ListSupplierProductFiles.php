<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListSupplierProductFiles\ListSupplierProductFiles as ListSupplierProductFilesQuery;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListSupplierProductFiles\ListSupplierProductFilesHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListSupplierProductFiles as ListProductFilesForSupplierPort;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ListSupplierProductFiles
{
    public function __construct(
        private ListSupplierProductFilesHandler $listSupplierProductFilesHandler,
    ) {
    }

    public function __invoke(Request $request, string $supplierIdentifier): JsonResponse
    {
        $productFiles = ($this->listSupplierProductFilesHandler)(
            new ListSupplierProductFilesQuery($supplierIdentifier, $request->query->getInt('page', 1))
        );

        return new JsonResponse([
            'product_files' => array_map(
                function (ProductFile $productFile) {
                    $productFile = $productFile->toArray();
                    $productFile['uploadedAt'] = (new \DateTimeImmutable($productFile['uploadedAt']))->format('c');

                    return $productFile;
                },
                $productFiles->productFiles,
            ),
            'total' => $productFiles->totalProductFilesCount,
            'items_per_page' => ListProductFilesForSupplierPort::NUMBER_OF_PRODUCT_FILES_PER_PAGE,
        ]);
    }
}
