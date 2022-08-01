<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model;

final class Supplier
{
    public function __construct(
        public string $identifier,
        public string $code,
        public string $label,
    ) {
    }

    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'code' => $this->code,
            'label' => $this->label,
        ];
    }
}
