<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImportStatus;

final class ProductFileWithMetadataAndComments
{
    public function __construct(
        public readonly string $identifier,
        public readonly string $originalFilename,
        public readonly ?string $path,
        public readonly ?string $uploadedByContributor,
        public readonly string $uploadedBySupplier,
        public readonly ?string $uploadedAt,
        public ?string $importStatus,
        public readonly ?string $importDate,
        public readonly array $retailerComments = [],
        public readonly array $supplierComments = [],
        public readonly ?string $retailerLastReadAt = null,
        public readonly ?string $supplierLastReadAt = null,
    ) {
        $this->importStatus = $importStatus ?? ProductFileImportStatus::TO_IMPORT->value;
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
            'importStatus' => $this->importStatus,
            'importDate' => $this->importDate,
        ];
    }
}
