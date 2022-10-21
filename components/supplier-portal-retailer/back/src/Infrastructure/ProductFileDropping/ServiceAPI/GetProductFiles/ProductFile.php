<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFiles;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile as ProductFileReadModel;

final class ProductFile
{
    private function __construct(
        public string $identifier,
        public string $originalFilename,
        public string $path,
        public ?string $uploadedByContributor,
        public string $uploadedBySupplier,
        public ?string $uploadedAt,
        public array $retailerComments = [],
        public array $supplierComments = [],
        public ?string $retailerLastReadAt = null,
        public ?string $supplierLastReadAt = null,
    ) {
    }

    public static function fromReadModel(ProductFileReadModel $productFile): self
    {
        return new self(
            $productFile->identifier,
            $productFile->originalFilename,
            $productFile->path,
            $productFile->uploadedByContributor,
            $productFile->uploadedBySupplier,
            $productFile->uploadedAt,
            $productFile->retailerComments,
            $productFile->supplierComments,
            $productFile->retailerLastReadAt,
            $productFile->supplierLastReadAt,
        );
    }

    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'originalFilename' => $this->originalFilename,
            'path' => $this->path,
            'uploadedByContributor' => $this->uploadedByContributor,
            'uploadedBySupplier' => $this->uploadedBySupplier,
            'uploadedAt' => $this->uploadedAt,
            'retailerComments' => $this->retailerComments,
            'supplierComments' => $this->supplierComments,
            'retailerLastReadAt' => $this->retailerLastReadAt,
            'supplierLastReadAt' => $this->supplierLastReadAt,
        ];
    }
}
