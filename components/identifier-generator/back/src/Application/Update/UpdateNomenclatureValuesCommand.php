<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

class UpdateNomenclatureValuesCommand
{
    /**
     * @param array<string, ?string> $values
     */
    public function __construct(
        private ?string $operator = null,
        private ?int $value = null,
        private ?array $values = [],
    ) {
    }

    /**
     * @return array<string, ?string>
     */
    public function getValues()
    {
        return $this->values;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function getValue()
    {
    }
}
