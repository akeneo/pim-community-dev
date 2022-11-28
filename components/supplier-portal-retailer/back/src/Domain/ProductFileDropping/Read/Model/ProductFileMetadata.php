<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model;

final class ProductFileMetadata
{
    public function __construct(public readonly int $numberOfRows, public readonly int $numberOfColumns)
    {
    }

    public function toArray(): array
    {
        return [
            'number_of_rows' => $this->numberOfRows,
            'number_of_columns' => $this->numberOfColumns,
        ];
    }
}
