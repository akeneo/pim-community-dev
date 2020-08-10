<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO;

use Webmozart\Assert\Assert;

final class AddAction implements ActionInterface
{
    public $field;
    public $items;
    public $scope;
    public $locale;

    public function __construct(array $data)
    {
        $this->field = $data['field'] ?? null;
        $this->items = $data['items'] ?? null;
        $this->scope = $data['scope'] ?? null;
        $this->locale = $data['locale'] ?? null;
    }

    public function toArray(): array
    {
        Assert::stringNotEmpty($this->field);
        Assert::isArray($this->items);
        Assert::nullOrStringNotEmpty($this->scope);
        Assert::nullOrStringNotEmpty($this->locale);

        return array_filter([
            'type' => 'add',
            'field' => $this->field,
            'items' => $this->items,
            'scope' => $this->scope,
            'locale' => $this->locale,
        ], function ($value): bool {
            return null !== $value;
        });
    }
}
