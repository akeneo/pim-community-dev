<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\GetProductFiles;

final class GetProductFilesQuery
{
    public function __construct(public string $contributorEmail, public int $page)
    {
    }
}
