<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\ProductFileImportStatus;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport\ImportExecutionId;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport\ProductFileIdentifier;

final class ProductFileImport
{
    private readonly ProductFileIdentifier $productFileIdentifier;
    private readonly ImportExecutionId $importExecutionId;

    private function __construct(
        string $productFileIdentifier,
        int $importExecutionId,
        private ProductFileImportStatus $fileImportStatus,
        private ?\DateTimeImmutable $finishedAt = null,
    ) {
        $this->productFileIdentifier = ProductFileIdentifier::fromString($productFileIdentifier);
        $this->importExecutionId = new ImportExecutionId($importExecutionId);
    }

    public static function start(ProductFile $productFile, int $importExecutionId): self
    {
        return new self(
            $productFile->identifier(),
            $importExecutionId,
            ProductFileImportStatus::IN_PROGRESS,
        );
    }

    public function productFileIdentifier(): string
    {
        return (string) $this->productFileIdentifier;
    }

    public function importExecutionId(): int
    {
        return $this->importExecutionId->getId();
    }

    public function fileImportStatus(): string
    {
        return $this->fileImportStatus->value;
    }

    public function completedAt(\DateTimeImmutable $completedAt): void
    {
        $this->fileImportStatus = ProductFileImportStatus::COMPLETED;
        $this->finishedAt = $completedAt;
    }

    public function failedAt(\DateTimeImmutable $failedAtDate): void
    {
        $this->fileImportStatus = ProductFileImportStatus::FAILED;
        $this->finishedAt = $failedAtDate;
    }

    public function finishedAt(): ?string
    {
        return $this->finishedAt?->format('Y-m-d H:i:s');
    }

    public static function hydrate(
        string $productFileIdentifier,
        int $importExecutionId,
        string $fileImportStatus,
        ?\DateTimeImmutable $finishedAt,
    ): self {
        return new self(
            $productFileIdentifier,
            $importExecutionId,
            ProductFileImportStatus::from($fileImportStatus),
            $finishedAt,
        );
    }
}
