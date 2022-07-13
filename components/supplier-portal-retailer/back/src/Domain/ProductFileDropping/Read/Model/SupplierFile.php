<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model;

final class SupplierFile
{
    public function __construct(
        public string $filename,
        public string $uploadedByContributor,
        public string $uploadedBySupplier,
        public ?string $uploadedAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'filename' => $this->filename,
            'uploadedByContributor' => $this->uploadedByContributor,
            'uploadedBySupplier' => $this->uploadedBySupplier,
            'uploadedAt' => $this->uploadedAt,
        ];
    }
}
