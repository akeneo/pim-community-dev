<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Exception\UnableToReadProductFile;

interface DownloadStoredProductFile
{
    /**
     * @throws UnableToReadProductFile
     * @throws ProductFileDoesNotExist
     * @phpstan-ignore-next-line
     */
    public function __invoke(string $productFilePath);
}
