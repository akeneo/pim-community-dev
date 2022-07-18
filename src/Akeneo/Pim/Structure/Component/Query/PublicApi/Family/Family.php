<?php

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

class Family
{
    public function __construct(
        private string $code,
        private array $labels,
    ) {
    }

    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'labels' => $this->labels,
        ];
    }
}
