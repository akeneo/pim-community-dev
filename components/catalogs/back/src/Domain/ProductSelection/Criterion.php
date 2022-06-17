<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Domain\ProductSelection;

class Criterion
{
    public function __construct(
        private string $field,
        private string $operator,
        private mixed $value = null,
    ) {
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
