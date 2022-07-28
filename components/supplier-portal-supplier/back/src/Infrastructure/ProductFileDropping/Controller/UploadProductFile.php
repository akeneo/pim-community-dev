<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\CreateSupplierFile;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\CreateSupplierFileHandler;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\InvalidSupplierFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UploadProductFile
{
    public function __construct(
        private CreateSupplierFileHandler $createSupplierFileHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('file');
        if (null === $uploadedFile) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $createSupplierFile = new CreateSupplierFile(
            $uploadedFile,
            $uploadedFile->getClientOriginalName(),
        );

        try {
            ($this->createSupplierFileHandler)($createSupplierFile);
        } catch (InvalidSupplierFile $e) {
            return new JsonResponse(['error' => $e->violations()[0]->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }
}
