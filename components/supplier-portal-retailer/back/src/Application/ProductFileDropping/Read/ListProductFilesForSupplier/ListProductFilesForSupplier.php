<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFilesForSupplier;

final class ListProductFilesForSupplier
{
    public function __construct(public string $contributorEmail, public int $page, public string $search = '')
    {
    }
}
