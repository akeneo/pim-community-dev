<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\DownloadProductFile as DownloadProductFileCommand;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\DownloadProductFileHandler;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\ProductFileIsNotDownloadable;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class DownloadProductFile
{
    public function __construct(
        private DownloadProductFileHandler $downloadProductFileHandler,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(string $identifier): Response
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if (null === $user) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        try {
            $supplierFileNameAndResourceFile = ($this->downloadProductFileHandler)(
                new DownloadProductFileCommand($identifier, $user->getUserIdentifier()),
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
