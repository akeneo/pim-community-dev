<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Family\ServiceAPI\Query;

final class FamilyWithLabels
{
    public function __construct(
        private string $code,
        private array $labels,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(): array
    {
        return [
            'code' => $this->code,
            'labels' => $this->labels,
        ];
    }
}
