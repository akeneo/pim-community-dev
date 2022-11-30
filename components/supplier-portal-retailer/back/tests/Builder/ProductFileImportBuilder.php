<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Builder;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImportStatus;
use Akeneo\SupplierPortal\Retailer\Test\Unit\Fakes\FrozenClock;

final class ProductFileImportBuilder
{
    private const DEFAULT_IMPORTED_DATE = '2022-11-28 15:24:38';
    private ProductFile $productFile;
    private int $importExecutionId;
    private ?ProductFileImportStatus $importStatus;
    private ?\DateTimeImmutable $importedAt = null;

    public function withProductFile(ProductFile $productFile): self
    {
        $this->productFile = $productFile;

        return $this;
    }

    public function withImportExecutionId(int $importExecutionId): self
    {
        $this->importExecutionId = $importExecutionId;

        return $this;
    }

    public function importedAt(\DateTimeImmutable $importedAt): self
    {
        $this->importedAt = $importedAt;

        return $this;
    }

    public function withImportStatus(ProductFileImportStatus $importStatus): self
    {
        $this->importStatus = $importStatus;

        return $this;
    }

    public function build(): ProductFileImport
    {
        $productFileImport = ProductFileImport::start(
            $this->productFile,
            $this->importExecutionId,
        );

        switch ($this->importStatus->value) {
            case ProductFileImportStatus::COMPLETED->value:
                $productFileImport->completedAt(
                    $this->importedAt ?? (new FrozenClock(self::DEFAULT_IMPORTED_DATE))->now(),
                );
                break;
            case ProductFileImportStatus::FAILED->value:
                $productFileImport->failedAt(
                    $this->importedAt ?? (new FrozenClock(self::DEFAULT_IMPORTED_DATE))->now(),
                );
                break;
        }

        return $productFileImport;
    }
}
