<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;

final class ProductFile
{
    public function __construct(
        public string $identifier,
        public string $originalFilename,
        public ?string $path,
        public ?string $uploadedByContributor,
        public string $uploadedBySupplier,
        public ?string $uploadedAt,
        public ?string $importStatus,
        public array $retailerComments = [],
        public array $supplierComments = [],
        public ?string $retailerLastReadAt = null,
        public ?string $supplierLastReadAt = null,
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
        ];
    }
}
