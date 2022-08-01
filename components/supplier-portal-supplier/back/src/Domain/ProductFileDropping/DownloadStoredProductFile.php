<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Path;

interface DownloadStoredProductFile
{
    //@phpstan-ignore-next-line
    public function __invoke(Path $path);
}
