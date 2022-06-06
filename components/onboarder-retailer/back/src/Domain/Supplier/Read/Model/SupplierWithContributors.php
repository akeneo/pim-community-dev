<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read\Model;

final class SupplierWithContributors
{
    public function __construct(
        public string $identifier,
        public string $code,
        public string $label,
        public array $contributors,
    ) {
    }

    public function toArray(): array
    {
        return [
            'identifier' => $this->identifier,
            'code' => $this->code,
            'label' => $this->label,
            'contributors' => $this->contributors,
        ];
    }
}
