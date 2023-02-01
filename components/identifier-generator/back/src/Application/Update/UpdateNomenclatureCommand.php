<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CommandInterface;

class UpdateNomenclatureCommand implements CommandInterface
{
    /**
     * @param array<string, ?string> $values
     */
    public function __construct(
        private readonly string $propertyType,
        private readonly string $operator,
        private readonly string|int $value,
        private readonly ?array $values = [],
    ) {
    }

    /**
     * @return array<string, ?string>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
