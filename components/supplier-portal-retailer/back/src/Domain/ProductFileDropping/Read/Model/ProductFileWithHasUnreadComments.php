<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImportStatus;

final class ProductFileWithHasUnreadComments
{
    public function __construct(
        public string $identifier,
        public string $originalFilename,
        public ?string $path,
        public ?string $uploadedByContributor,
        public string $uploadedBySupplier,
        public ?string $uploadedAt,
        public bool $hasUnreadComments,
        public ?string $importStatus,
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
            'hasUnreadComments' => $this->hasUnreadComments,
            'importStatus' => $this->importStatus,
        ];
    }
}
