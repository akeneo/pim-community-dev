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
    public $text;
    public $newLine;

    public function __construct(array $data)
    {
        $this->field = $data['field'] ?? null;
        $this->scope = $data['scope'] ?? null;
        $this->locale = $data['locale'] ?? null;
        $this->labelLocale = $data['label_locale'] ?? null;
        $this->format = $data['format'] ?? null;
        $this->currency = $data['currency'] ?? null;
        $this->text = $data['text'] ?? null;
        $this->newLine = array_key_exists('new_line', $data) ? true : null;
    }

    public function toArray(): array
    {
        if (null !== $this->text) {
            return ['text' => $this->text];
        }

        if (true === $this->newLine) {
            return ['new_line' => null];
        }

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
