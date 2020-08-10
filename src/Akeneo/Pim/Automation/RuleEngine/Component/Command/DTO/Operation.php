<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

use Webmozart\Assert\Assert;

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

    public function toArray(): array
    {
        Assert::oneOf($this->operator, ['add', 'subtract', 'multiply', 'divide']);
        Assert::nullOrStringNotEmpty($this->field);
        Assert::nullOrStringNotEmpty($this->currency);
        Assert::nullOrStringNotEmpty($this->scope);
        Assert::nullOrStringNotEmpty($this->locale);
        Assert::nullOrNumeric($this->value);

        return array_filter([
            'operator' => $this->operator,
            'field' => $this->field,
            'scope' => $this->scope,
            'locale' => $this->locale,
            'currency' => $this->currency,
            'value' => null === $this->value ? null : (float)$this->value,
        ],
        function ($value): bool {
            return null !== $value;
        });
    }
}
