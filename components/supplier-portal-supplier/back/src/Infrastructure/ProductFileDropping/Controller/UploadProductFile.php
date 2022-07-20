<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UploadProductFile
{
    public function __construct()
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $file = $request->files->has('file') ? $request->files->get('file') : null;

        if (null === $file) {
            return new JsonResponse(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

//        try {
//            //Call handler
//        } catch (\Exception) {
//            return new JsonResponse(['error' => 'Sample error message'], Response::HTTP_UNPROCESSABLE_ENTITY);
//        }

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }
}
