<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\DownloadProductFile as DownloadProductFileCommand;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\DownloadProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Exception\SupplierFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Exception\SupplierFileIsNotDownloadable;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use Akeneo\UserManagement\Component\Model\User;
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
        /** @var ?User $user */
        $user = $this->tokenStorage->getToken()?->getUser();
        if (null === $user) {
            return new JsonResponse(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $productFileNameAndResourceFile = ($this->downloadProductFileHandler)(
                new DownloadProductFileCommand($identifier, $user->getId())
            );

            if (null === $stream) {
                return new Response(null, Response::HTTP_NOT_FOUND);
            }
        } catch (SupplierFileDoesNotExist | SupplierFileIsNotDownloadable) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $headers['Content-Disposition'] = sprintf(
            'attachment; filename="%s.xlsx"',
            $productFileNameAndResourceFile->originalFilename,
        );

        return new StreamedFileResponse($productFileNameAndResourceFile->file, Response::HTTP_OK, $headers);
    }
}
