<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\ProductFileImportRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\UpdateProductFileImportStatusFromJobStatus as UpdateProductFileImportStatus;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;

final class UpdateProductFileImportStatusFromJobStatus implements UpdateProductFileImportStatus
{
    public function __construct(private readonly ProductFileImportRepository $productFileImportRepository)
    {
    }

    public function __invoke(int $jobStatus, int $jobExecutionId): void
    {
        $productFileImport = $this->productFileImportRepository->findByJobExecutionId($jobExecutionId);

        if (null === $productFileImport) {
            return;
        }

        $productFileImport = $this->updateProductFileImport($productFileImport, $jobStatus);

        $this->productFileImportRepository->save($productFileImport);
    }

    private function updateProductFileImport(ProductFileImport $productFileImport, int $status): ProductFileImport
    {
        switch ($status) {
            case BatchStatus::STOPPING:
            case BatchStatus::STOPPED:
            case BatchStatus::FAILED:
            case BatchStatus::ABANDONED:
            case BatchStatus::UNKNOWN:
                $productFileImport->failedAt(new \DateTimeImmutable());
                break;
            case BatchStatus::COMPLETED:
                $productFileImport->completedAt(new \DateTimeImmutable());
                break;
        }

        return $productFileImport;
    }
}
