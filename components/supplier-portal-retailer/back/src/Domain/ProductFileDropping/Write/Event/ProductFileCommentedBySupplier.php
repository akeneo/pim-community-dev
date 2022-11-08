<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event;

final class ProductFileCommentedBySupplier
{
    public function __construct(
        private readonly string $productFileIdentifier,
        private readonly string $commentContent,
        private readonly string $authorEmail,
    ) {
    }

    public function productFileIdentifier(): string
    {
        return $this->productFileIdentifier;
    }

    public function commentContent(): string
    {
        return $this->commentContent;
    }

    public function authorEmail(): string
    {
        return $this->authorEmail;
    }
}
