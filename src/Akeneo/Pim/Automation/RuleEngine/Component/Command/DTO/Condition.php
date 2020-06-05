<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

final class Condition
{
    public $field;
    public $operator;
    public $value;
    public $scope;
    public $locale;

    public function __construct(array $data)
    {
        $this->field = $data['field'] ?? null;
        $this->operator = $data['operator'] ?? null;
        $this->value = $data['value'] ?? null;
        $this->scope = $data['scope'] ?? null;
        $this->locale = $data['locale'] ?? null;
    }
}
