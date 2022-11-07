<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport\ImportExecutionId;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport\ProductFileIdentifier;

final class ProductFileImport
{
    //Do we use the Product File Dropping's VO ?
    private readonly ProductFileIdentifier $productFileIdentifier;
    private readonly ImportExecutionId $importExecutionId;


    private function __construct(string $productFileIdentifier, int $importExecutionId, private ProductFileImportStatus $fileImportStatus)
    {
        $this->productFileIdentifier = ProductFileIdentifier::fromString($productFileIdentifier);
        $this->importExecutionId = new ImportExecutionId($importExecutionId);
    }

    public static function start(string $productFileIdentifier, int $importExecutionId): self
    {
        return new self($productFileIdentifier, $importExecutionId, ProductFileImportStatus::IN_PROGRESS);
    }

    public function productFileIdentifier(): string
    {
        return $this->productFileIdentifier->__toString();
    }

    public function importExecutionId(): int
    {
        return $this->importExecutionId->getId();
    }

    public function fileImportStatus(): ProductFileImportStatus
    {
        return $this->fileImportStatus;
    }
}
