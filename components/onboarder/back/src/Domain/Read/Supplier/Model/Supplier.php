<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Read\Supplier\Model;

final class Supplier
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
        $contributors = array_map(fn (Contributor $contributor) => $contributor->toArray(), $this->contributors);

        return [
            'identifier' => $this->identifier,
            'code' => $this->code,
            'label' => $this->label,
            'contributors' => $contributors,
        ];
    }
}
