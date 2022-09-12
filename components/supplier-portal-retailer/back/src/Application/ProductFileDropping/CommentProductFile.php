<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping;

final class CommentProductFile
{
    public function __construct(
        public string $productFileIdentifier,
        public string $authorEmail,
        public string $content,
        public \DateTimeImmutable $createdAt,
    ) {
    }
}
