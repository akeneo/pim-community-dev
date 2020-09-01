<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

use Webmozart\Assert\Assert;

final class ClearAction implements ActionInterface
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

    public function toArray(): array
    {
        Assert::stringNotEmpty($this->field);
        Assert::nullOrStringNotEmpty($this->scope);
        Assert::nullOrStringNotEmpty($this->locale);

        return array_filter([
            'type' => 'clear',
            'field' => $this->field,
            'scope' => $this->scope,
            'locale' => $this->locale,
        ], function ($value): bool {
            return null !== $value;
        });
    }
}
