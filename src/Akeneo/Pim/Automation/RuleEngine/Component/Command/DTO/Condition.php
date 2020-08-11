<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

use Webmozart\Assert\Assert;

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

    public function toArray(): array
    {
        Assert::stringNotEmpty($this->field);
        Assert::stringNotEmpty($this->operator);
        Assert::nullOrStringNotEmpty($this->scope);
        Assert::nullOrStringNotEmpty($this->locale);

        return array_filter([
            'field' => $this->field,
            'operator' => $this->operator,
            'value' => $this->value,
            'scope' => $this->scope,
            'locale' => $this->locale,
        ], function ($value): bool {
            return null !== $value;
        });
    }
}
