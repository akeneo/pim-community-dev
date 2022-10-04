<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile\DownloadProductFile as DownloadProductFileServiceAPI;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile\DownloadProductFileQuery;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\DownloadProductFile\Exception\ProductFileNotFound;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Security\ContributorAccount;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class DownloadProductFile
{
    public function __construct(
        private DownloadProductFileServiceAPI $downloadProductFile,
    ) {
    }

    public function __invoke(#[CurrentUser] ContributorAccount $user, string $productFileIdentifier): Response
    {
        try {
            $productFile = ($this->downloadProductFile)(
                new DownloadProductFileQuery($productFileIdentifier, $user->getUserIdentifier())
            );
        } catch (ProductFileNotFound) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $headers['Content-Disposition'] = sprintf(
            'attachment; filename="%s"',
            $productFile->originalFilename,
        );

        return new StreamedFileResponse($productFile->file, Response::HTTP_OK, $headers);
    }
}
