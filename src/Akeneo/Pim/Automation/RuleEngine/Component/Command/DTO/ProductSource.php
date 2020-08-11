<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

use Webmozart\Assert\Assert;

final class ProductSource
{
    public $field;
    public $scope;
    public $locale;
    public $labelLocale;
    public $format;
    public $currency;

    public function __construct(array $data)
    {
        $this->field = $data['field'] ?? null;
        $this->scope = $data['scope'] ?? null;
        $this->locale = $data['locale'] ?? null;
        $this->labelLocale = $data['label_locale'] ?? null;
        $this->format = $data['format'] ?? null;
        $this->currency = $data['currency'] ?? null;
    }

    public function toArray(): array
    {
        Assert::stringNotEmpty($this->field);
        Assert::nullOrStringNotEmpty($this->scope);
        Assert::nullOrStringNotEmpty($this->locale);
        Assert::nullOrStringNotEmpty($this->labelLocale);
        Assert::nullOrStringNotEmpty($this->format);
        Assert::nullOrStringNotEmpty($this->currency);

        return array_filter([
            'field' => $this->field,
            'scope' => $this->scope,
            'locale' => $this->locale,
            'label_locale' => $this->labelLocale,
            'format' => $this->format,
            'currency' => $this->currency,
        ],
        function ($value): bool {
            return null !== $value;
        });
    }
}
