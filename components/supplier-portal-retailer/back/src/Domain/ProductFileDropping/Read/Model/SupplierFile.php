<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model;

final class SupplierFile
{
    public function __construct(
        public string $identifier,
        public string $path,
        public ?string $uploadedByContributor,
        public string $uploadedBySupplier,
        public ?string $uploadedAt,
    ) {
    }

    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'path' => $this->path,
            'uploadedByContributor' => $this->uploadedByContributor,
            'uploadedBySupplier' => $this->uploadedBySupplier,
            'uploadedAt' => (new \DateTimeImmutable($this->uploadedAt))->format('c'),
        ];
    }
}
