<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Webmozart\Assert\Assert;

class Enabled implements ConditionInterface
{
    public function __construct(
        private bool $value
    ) {
    }

    public static function type(): string
    {
        return 'enabled';
    }

    public static function fromBoolean(bool $value)
    {
        return new self($value);
    }

    public static function fromNormalized(array $normalizedProperty): ConditionInterface
    {
        Assert::keyExists($normalizedProperty, 'type');
        Assert::eq($normalizedProperty['type'], self::type());
        Assert::keyExists($normalizedProperty, 'value');
        Assert::boolean($normalizedProperty['value']);

        return self::fromBoolean($normalizedProperty['value']);
    }

    public function normalize(): array
    {
        return [
            'type' => self::type(),
            'value' => $this->value,
        ];
    }
}
