<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\CreateSupplierFile;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\CreateSupplierFileHandler;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

final class UploadProductFile
{
    private const SUPPLIER_FILES_TMP_UPLOAD_DIR = 'supplier_files';
    public function __construct(
        private CreateSupplierFileHandler $createSupplierFileHandler,
        private TokenStorageInterface $tokenStorage,
        private SluggerInterface $slugger,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $file = $request->files->has('file') ? $request->files->get('file') : null;
        if (null === $file) {
            return new JsonResponse(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if (null === $user) {
            return new JsonResponse(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

            $temporaryPath = $file->move(
                sprintf(
                    '%s/%s',
                    sys_get_temp_dir(),
                    self::SUPPLIER_FILES_TMP_UPLOAD_DIR,
                ),
                $newFilename,
            );
        } catch (FileException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $createSupplierFile = new CreateSupplierFile(
            $file->getClientOriginalName(),
            $temporaryPath->getPathName(),
            $user->getUserIdentifier(),
        );

        try {
            ($this->createSupplierFileHandler)($createSupplierFile);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }
}
