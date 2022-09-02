<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFiles;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFile as ProductFileReadModel;

final class ProductFile
{
    public function __construct(
        public string $identifier,
        public string $originalFilename,
        public ?string $uploadedByContributor,
        public string $uploadedBySupplier,
        public ?string $uploadedAt,
    ) {
    }

    public static function fromReadModel(ProductFileReadModel $productFileReadModel): self
    {
        return new self(
            $productFileReadModel->identifier,
            $productFileReadModel->originalFilename,
            $productFileReadModel->uploadedByContributor,
            $productFileReadModel->uploadedBySupplier,
            $productFileReadModel->uploadedAt,
        );
    }

    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'originalFilename' => $this->originalFilename,
            'uploadedByContributor' => $this->uploadedByContributor,
            'uploadedBySupplier' => $this->uploadedBySupplier,
            'uploadedAt' => $this->uploadedAt, // @todo Move the formatting to the Controller in supplier app (format('c'))
        ];
    }
}
