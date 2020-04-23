<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

final class ProductSource
{
    public $field;
    public $scope;
    public $locale;
    public $options;

    public function __construct(array $data)
    {
        $this->field = $data['field'] ?? null;
        $this->scope = $data['scope'] ?? null;
        $this->locale = $data['locale'] ?? null;
        $this->options = $data['options'] ?? null;
    }
}
