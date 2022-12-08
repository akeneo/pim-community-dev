<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Spout\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilePreview;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFilePreview;

final class InMemoryGetProductFilePreview implements GetProductFilePreview
{
    private array $productFileReview = [];

    public function __invoke(string $productFilePath, string $productFileName): ProductFilePreview
    {
        return new ProductFilePreview($this->productFileReview);
    }

    public function save(array $productFileReview): void
    {
        $this->productFileReview = $productFileReview;
    }
}
