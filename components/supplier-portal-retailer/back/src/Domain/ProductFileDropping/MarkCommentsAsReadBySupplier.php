<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface MarkCommentsAsReadBySupplier
{
    public function __invoke(string $productFileIdentifier, \DateTimeImmutable $readAt): void;
}
