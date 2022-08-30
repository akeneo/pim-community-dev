<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\DownloadProductFile as DownloadProductFileCommand;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\DownloadProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileIsNotDownloadable;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Event\ProductFileDownloaded;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use Akeneo\UserManagement\Component\Model\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class DownloadProductFile
{
    public function __construct(
        private DownloadProductFileHandler $downloadProductFileHandler,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(#[CurrentUser] User $user, string $identifier): Response
    {
        try {
            $productFileNameAndResourceFile = ($this->downloadProductFileHandler)(
                new DownloadProductFileCommand($identifier)
            );

        } catch (ProductFileDoesNotExist | ProductFileIsNotDownloadable) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $this->eventDispatcher->dispatch(new ProductFileDownloaded(
            $identifier,
            $user->getId(),
        ));

        $headers['Content-Disposition'] = sprintf(
            'attachment; filename="%s.xlsx"',
            $productFileNameAndResourceFile->originalFilename,
        );

        return new StreamedFileResponse($productFileNameAndResourceFile->file, Response::HTTP_OK, $headers);
    }
}
