<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model;

final class ProductFileImport
{
    public function __construct(private string $code, private string $label)
    {
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'label' => $this->label,
        ];
    }
}
