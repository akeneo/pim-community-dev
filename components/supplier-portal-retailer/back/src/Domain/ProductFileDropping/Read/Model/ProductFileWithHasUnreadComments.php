<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;

final class ProductFileWithHasUnreadComments
{
    public function __construct(
        public readonly string $identifier,
        public readonly string $originalFilename,
        public readonly ?string $path,
        public readonly ?string $uploadedByContributor,
        public readonly string $uploadedBySupplier,
        public readonly ?string $uploadedAt,
        public readonly bool $hasUnreadComments,
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
