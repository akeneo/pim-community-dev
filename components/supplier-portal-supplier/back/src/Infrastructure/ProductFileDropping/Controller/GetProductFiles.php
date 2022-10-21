<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFiles\GetProductFiles as GetProductFilesServiceAPI;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFiles\GetProductFilesQuery;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFiles\ProductFile;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Security\ContributorAccount;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class GetProductFiles
{
    public function __construct(private GetProductFilesServiceAPI $getProductFiles)
    {
    }

    public function __invoke(Request $request, #[CurrentUser] ContributorAccount $user): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $productFiles = ($this->getProductFiles)(new GetProductFilesQuery($user->getUserIdentifier(), $page));

        return new JsonResponse(
            [
                'product_files' => array_map(
                    function (ProductFile $productFile) {
                        $productFile = $productFile->toArray();
                        $productFile['uploadedAt'] = (new \DateTimeImmutable($productFile['uploadedAt']))->format('c');
                        $productFile['supplierComments'] = array_map(function (array $comment) {
                            $comment['created_at'] = (new \DateTimeImmutable($comment['created_at']))->format('c');
                            return $comment;
                        }, $productFile['supplierComments']);
                        $productFile['retailerComments'] = array_map(function (array $comment) {
                            $comment['created_at'] = (new \DateTimeImmutable($comment['created_at']))->format('c');
                            return $comment;
                        }, $productFile['retailerComments']);
                    $productFile['supplierLastReadAt'] = null === $productFile['supplierLastReadAt'] ? null : (new \DateTimeImmutable($productFile['supplierLastReadAt']))->format('c');
                    $productFile['retailerLastReadAt'] = null === $productFile['retailerLastReadAt'] ? null : (new \DateTimeImmutable($productFile['retailerLastReadAt']))->format('c');

                        return $productFile;
                    },
                    $productFiles->productFiles,
                ),
                'total' => $productFiles->numberTotalOfProductFiles,
            ],
            Response::HTTP_OK,
        );
    }
}
