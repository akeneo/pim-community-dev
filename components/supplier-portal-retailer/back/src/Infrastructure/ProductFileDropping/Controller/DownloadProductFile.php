<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\DownloadProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Exception\SupplierFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Exception\SupplierFileDownloadError;
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
            $stream = ($this->downloadProductFileHandler)(new \Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\DownloadProductFile($identifier));
        } catch (SupplierFileDoesNotExist | SupplierFileDownloadError) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $headers['Content-Disposition'] = sprintf(
            'attachment; filename="%s.xlsx"',
            $identifier,
        );

        return new StreamedFileResponse($stream, Response::HTTP_OK, $headers);
    }
}
