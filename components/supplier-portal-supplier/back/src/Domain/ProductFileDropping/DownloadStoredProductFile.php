<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping;

interface DownloadStoredProductFile
{
    //@phpstan-ignore-next-line
    public function __invoke(string $path);
}
