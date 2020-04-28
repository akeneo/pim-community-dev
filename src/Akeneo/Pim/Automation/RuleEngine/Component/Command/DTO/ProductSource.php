<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

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
}
