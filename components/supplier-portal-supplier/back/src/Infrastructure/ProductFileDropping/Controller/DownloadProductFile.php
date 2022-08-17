<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\DownloadProductFile as DownloadProductFileCommand;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\DownloadProductFileHandler;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\ProductFileIsNotDownloadable;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use Symfony\Component\HttpFoundation\Response;

final class DownloadProductFile
{
    public function __construct(private DownloadProductFileHandler $downloadProductFileHandler)
    {
    }

    public function __invoke(string $identifier): Response
    {
        try {
            $supplierFileNameAndResourceFile = ($this->downloadProductFileHandler)(
                new DownloadProductFileCommand($identifier)
            );
        } catch (ProductFileDoesNotExist | ProductFileIsNotDownloadable) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $headers['Content-Disposition'] = sprintf(
            'attachment; filename="%s.xlsx"',
            $supplierFileNameAndResourceFile->originalFilename,
        );

        return new StreamedFileResponse($supplierFileNameAndResourceFile->file, Response::HTTP_OK, $headers);
    }
}
