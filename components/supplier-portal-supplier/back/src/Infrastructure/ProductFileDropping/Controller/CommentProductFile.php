<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\CommentProductFile as CommentProductFileServiceAPI;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\CommentProductFileCommand;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead\MarkCommentsAsRead;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead\MarkCommentsAsReadCommand;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile\Exception\InvalidComment;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CommentProductFile
{
    public function __construct(private CommentProductFileServiceAPI $commentProductFileServiceAPI, private MarkCommentsAsRead $markCommentsAsRead)
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
            ($this->markCommentsAsRead)(
                new MarkCommentsAsReadCommand($productFileIdentifier, new \DateTimeImmutable())
            );
        } catch (InvalidComment $e) {
            return new JsonResponse($e->errorCode, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
