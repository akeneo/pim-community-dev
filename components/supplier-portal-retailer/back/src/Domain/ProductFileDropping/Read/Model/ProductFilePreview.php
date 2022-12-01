<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model;

final class ProductFilePreview
{
    public function __construct(public readonly array $preview)
    {
    }
}
