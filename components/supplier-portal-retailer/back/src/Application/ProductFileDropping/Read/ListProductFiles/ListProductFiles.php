<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\ListProductFiles;

final class ListProductFiles
{
    public function __construct(public readonly int $page, public readonly string $search)
    {
    }
}
