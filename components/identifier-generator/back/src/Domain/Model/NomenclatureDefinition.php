<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

class NomenclatureDefinition
{
    public function __construct(
        private readonly ?string $operator = null,
        private readonly ?int $value = null,
        // TODO Add checkbox
    ) {
    }

    public function operator(): ?string
    {
        return $this->operator;
    }

    public function withOperator(string $operator): self
    {
        return new NomenclatureDefinition($operator, $this->value);
    }

    public function value(): ?int
    {
        return $this->value;
    }

    public function withValue(int $value): self
    {
        return new NomenclatureDefinition($this->operator, $value);
    }

    public function normalize(): array
    {
        return [
            'operator' => $this->operator,
            'value' => $this->value,
        ];
    }
}
