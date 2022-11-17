<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport;

interface UpdateProductFileImportStatusFromJobStatus
{
    public function __invoke(int $jobStatus, int $jobExecutionId): void;
}
