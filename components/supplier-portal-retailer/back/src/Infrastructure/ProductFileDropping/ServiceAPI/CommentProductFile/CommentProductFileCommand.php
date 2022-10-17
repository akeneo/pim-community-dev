<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\CommentProductFile;

final class CommentProductFileCommand
{
    public function __construct(
        public string $productFileIdentifier,
        public string $authorEmail,
        public string $content,
        public \DateTimeImmutable $createdAt,
    ) {
    }
}
