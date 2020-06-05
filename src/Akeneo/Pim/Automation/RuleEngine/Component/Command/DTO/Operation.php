<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

final class Operation
{
    public $operator;
    public $field;
    public $scope;
    public $locale;
    public $value;
    public $currency;

    public function __construct(array $data)
    {
        $this->operator = $data['operator'] ?? null;
        $this->field = $data['field'] ?? null;
        $this->scope = $data['scope'] ?? null;
        $this->locale = $data['locale'] ?? null;
        $this->value = $data['value'] ?? null;
        $this->currency = $data['currency'] ?? null;
    }
}
