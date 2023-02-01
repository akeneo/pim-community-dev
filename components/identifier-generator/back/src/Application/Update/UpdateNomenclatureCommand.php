<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CommandInterface;

class UpdateNomenclatureCommand implements CommandInterface
{
    /**
     * @param array<string, ?string> $values
     */
    public function __construct(
        private readonly ?string $propertyCode,
        private readonly ?string $operator,
        private readonly string|int|null $value,
        private readonly ?bool $generateIfEmpty,
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

    public function getGenerateIfEmpty(): bool
    {
        return $this->generateIfEmpty;
    }
}
