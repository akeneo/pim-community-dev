<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model;

final class SupplierFile
{
    public function __construct(
        public string $identifier,
        public string $originalFilename,
        public string $path,
        public ?string $uploadedByContributor,
        public string $uploadedAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'original_filename' => $this->originalFilename,
            'path' => $this->path,
            'uploadedByContributor' => $this->uploadedByContributor,
            'uploadedAt' => $this->uploadedAt,
        ];
    }
}
