<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Read\Supplier\Model;

final class SupplierWithContributors
{
    public function __construct(
        public string $code,
        public string $label,
        public array $contributors,
    ) {
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'label' => $this->label,
            'contributors' => $this->contributors,
        ];
    }
}
