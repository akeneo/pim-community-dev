<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

class NomenclatureDefinition
{
    public function __construct(
        private readonly string $operator,
        private readonly int $value,
    ) {
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function withOperator(string $operator): self
    {
        return new NomenclatureDefinition($operator, $this->value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function withValue(int $value): self
    {
        return new NomenclatureDefinition($this->operator, $value);
    }
}
