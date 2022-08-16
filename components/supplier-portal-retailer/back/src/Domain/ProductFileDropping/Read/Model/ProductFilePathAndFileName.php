<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model;

final class ProductFilePathAndFileName
{
    public function __construct(public string $originalFilename, public string $path)
    {
    }
}
