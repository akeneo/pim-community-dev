<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CommentProductFile as CommentProductFileCommand;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CommentProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CommentProductFile
{
    public function __construct(
        private CommentProductFileHandler $commentProductFileHandler,
    ) {
    }

    public function __invoke(Request $request, string $productFileIdentifier): JsonResponse
    {
        $authorEmail = $request->get('authorEmail');
        $content = $request->get('content');
        if (null === $authorEmail || null === $content) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        try {
            ($this->commentProductFileHandler)(
                new CommentProductFileCommand($productFileIdentifier, $authorEmail, $content, new \DateTimeImmutable())
            );
        } catch (ProductFileDoesNotExist) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
