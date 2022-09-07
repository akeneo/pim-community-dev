<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model;

final class ProductFile
{
    public function __construct(
        public string $identifier,
        public string $originalFilename,
        public ?string $uploadedByContributor,
        public string $uploadedBySupplier,
        public ?string $uploadedAt,
        public array $comments = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'originalFilename' => $this->originalFilename,
            'uploadedByContributor' => $this->uploadedByContributor,
            'uploadedBySupplier' => $this->uploadedBySupplier,
            'uploadedAt' => $this->uploadedAt, // @todo Move the formatting to the Controller in supplier app (format('c'))
            'comments' => $this->comments,
        ];
    }
}
