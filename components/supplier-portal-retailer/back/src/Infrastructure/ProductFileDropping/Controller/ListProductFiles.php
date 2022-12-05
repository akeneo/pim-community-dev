<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFiles\ListProductFiles as ListProductFilesQuery;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFiles\ListProductFilesHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\ListProductFiles as ListProductFilesPort;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileWithHasUnreadComments;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ListProductFiles
{
    public function __construct(
        private readonly ListProductFilesHandler $listProductFilesHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $productFiles = ($this->listProductFilesHandler)(
            new ListProductFilesQuery(
                $request->query->getInt('page', 1),
                trim($request->query->get('search', '')),
                $request->query->get('status'),
            )
        );

        return new JsonResponse([
            'product_files' => array_map(
                function (ProductFileWithHasUnreadComments $productFile) {
                    $productFile = $productFile->toArray();
                    $productFile['uploadedAt'] = (new \DateTimeImmutable($productFile['uploadedAt']))->format('c');

                    return $productFile;
                },
                $productFiles->productFiles,
            ),
            'total' => $productFiles->totalProductFilesCount,
            'total_search_results' => $productFiles->searchResultsCount,
            'items_per_page' => ListProductFilesPort::NUMBER_OF_PRODUCT_FILES_PER_PAGE,
        ]);
    }
}
