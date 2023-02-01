<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

class NomenclatureDefinition
{
    public function __construct(
        private readonly ?string $operator = null,
        private readonly ?int $value = null,
        private readonly ?bool $generateIfEmpty = null,
    ) {
    }

    public function generateIfEmpty(): ?bool
    {
        return $this->generateIfEmpty;
    }

    public function operator(): ?string
    {
        return $this->operator;
    }

    public function value(): ?int
    {
        return $this->value;
    }

    public function withOperator(string $operator): self
    {
        return new NomenclatureDefinition($operator, $this->value, $this->generateIfEmpty);
    }

    public function withValue(int $value): self
    {
        return new NomenclatureDefinition($this->operator, $value, $this->generateIfEmpty);
    }

    public function withGenerateIfEmpty(bool $generateIfEmpty): self
    {
        return new NomenclatureDefinition($this->operator, $this->value, $generateIfEmpty);
    }

    public function normalize(): array
    {
        return [
            'operator' => $this->operator,
            'value' => $this->value,
            'generate_if_empty' => $this->generateIfEmpty,
        ];
    }
}
