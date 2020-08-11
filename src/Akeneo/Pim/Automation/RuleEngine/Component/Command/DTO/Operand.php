<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

use Webmozart\Assert\Assert;

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

    public function toArray(): array
    {
        Assert::nullOrStringNotEmpty($this->field);
        Assert::nullOrStringNotEmpty($this->currency);
        Assert::nullOrStringNotEmpty($this->scope);
        Assert::nullOrStringNotEmpty($this->locale);
        Assert::nullOrNumeric($this->value);

        return array_filter([
            'field' => $this->field,
            'scope' => $this->scope,
            'locale' => $this->locale,
            'currency' => $this->currency,
            'value' => null === $this->value ? null : (float)$this->value,
        ], function ($value): bool {
            return null !== $value;
        });
    }
}
