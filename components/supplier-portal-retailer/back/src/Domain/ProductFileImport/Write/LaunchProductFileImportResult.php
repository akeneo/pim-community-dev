<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write;

final class LaunchProductFileImportResult
{
    public function __construct(
        public readonly int $importExecutionId,
        public readonly string $importExecutionUrl,
    ) {
    }
}
