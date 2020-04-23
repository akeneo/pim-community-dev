<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

final class Operand
{
    public $field;
    public $currency;
    public $value;
    public $scope;
    public $locale;

    public function __construct($data)
    {
        $this->field = $data['field'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->value = $data['value'] ?? null;
        $this->scope = $data['scope'] ?? null;
        $this->locale = $data['locale'] ?? null;
    }
}
