<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile\Exception\InvalidUploadedProductFile;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile\Exception\UnableToStoreProductFile;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile\UploadProductFile as UploadProductFileServiceAPI;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile\UploadProductFileCommand;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Security\ContributorAccount;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class UploadProductFile
{
    public function __construct(
        private UploadProductFileServiceAPI $uploadProductFile,
    ) {
    }

    public function __invoke(#[CurrentUser] ContributorAccount $user, Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('file');
        if (null === $uploadedFile) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        try {
            ($this->uploadProductFile)(new UploadProductFileCommand($uploadedFile, $user->getUserIdentifier()));
        } catch (InvalidUploadedProductFile $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (UnableToStoreProductFile) {
            return new JsonResponse([], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
