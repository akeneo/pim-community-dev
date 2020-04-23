<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

final class ClearAction
{
    public $field;
    public $scope;
    public $locale;

    public function __construct(array $action)
    {
        $this->field = $action['field'] ?? null;
        $this->scope = $action['scope'] ?? null;
        $this->locale = $action['locale'] ?? null;
    }
}
