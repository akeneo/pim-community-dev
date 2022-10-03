<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\CommentProductFile as CommentProductFileServiceAPI;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\CommentProductFileCommand;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception\CommentTooLong;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception\EmptyComment;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception\MaxCommentPerProductFileReached;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CommentProductFile
{
    public function __construct(private CommentProductFileServiceAPI $commentProductFileServiceAPI)
    {
    }

    public function __invoke(Request $request, string $productFileIdentifier): JsonResponse
    {
        $authorEmail = $request->get('authorEmail');
        $content = $request->get('content');
        if (null === $authorEmail || null === $content) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        try {
            ($this->commentProductFileServiceAPI)(
                new CommentProductFileCommand($productFileIdentifier, $authorEmail, $content, new \DateTimeImmutable())
            );
        } catch (EmptyComment | CommentTooLong | MaxCommentPerProductFileReached $e) {
            return new JsonResponse($e->getErrorCode(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
