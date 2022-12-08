<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Read\PreviewProductFile;

final class PreviewProductFile
{
    public function __construct(public readonly string $productFileIdentifier)
    {
    }
}
