<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping;

interface GetProductFilePathsOfOldProductFiles
{
    public function __invoke(): array;
}
