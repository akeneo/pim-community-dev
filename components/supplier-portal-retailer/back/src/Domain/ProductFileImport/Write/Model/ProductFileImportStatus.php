<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model;

enum ProductFileImportStatus
{
    case IN_PROGRESS;
    case COMPLETED;
    case FAILED;
}
