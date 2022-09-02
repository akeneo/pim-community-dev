<?php

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface DeleteOldProductFiles
{
    public const RETENTION_DURATION_IN_DAYS = 90;

    public function __invoke(): void;
}
