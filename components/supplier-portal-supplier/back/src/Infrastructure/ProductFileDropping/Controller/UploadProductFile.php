<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\CreateSupplierFile;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\CreateSupplierFileHandler;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\InvalidProductFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class UploadProductFile
{
    public function __construct(
        private CreateSupplierFileHandler $createSupplierFileHandler,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $uploadedFile = $request->files->get('file');
        if (null === $uploadedFile) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if (null === $user) {
            return new JsonResponse(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $createSupplierFile = new CreateSupplierFile(
            $uploadedFile,
            $uploadedFile->getClientOriginalName(),
            $user->getUserIdentifier(),
        );

        try {
            ($this->createSupplierFileHandler)($createSupplierFile);
        } catch (InvalidProductFile $e) {
            return new JsonResponse(
                [
                    'error' => 0 < count($e->violations()) ? $e->violations()[0]->getMessage() : null,
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }
}
