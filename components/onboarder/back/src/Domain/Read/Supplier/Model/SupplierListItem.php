<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Read\Supplier\Model;

final class SupplierListItem
{
    public function __construct(
        public string $identifier,
        public string $code,
        public string $label,
        public int $contributorsCount,
    ) {
    }

    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'code' => $this->code,
            'label' => $this->label,
            'contributorsCount' => $this->contributorsCount,
        ];
    }
}
