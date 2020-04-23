<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

final class AddAction
{
    public $field;
    public $items;
    public $locale;
    public $scope;

    public function __construct(array $data)
    {
        $this->field = $data['field'] ?? null;
        $this->items = $data['items'] ?? null;
        $this->locale = $data['locale'] ?? null;
        $this->scope = $data['scope'] ?? null;
    }
}
