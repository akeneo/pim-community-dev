<?php

declare(strict_types=1);

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

    public static function fromBoolean(bool $value): self
    {
        return new self($value);
    }

    /**
     * @param array<string, boolean> $normalizedProperty
     */
    public static function fromNormalized(array $normalizedProperty): ConditionInterface
    {
        Assert::keyExists($normalizedProperty, 'type');
        Assert::eq($normalizedProperty['type'], self::type());
        Assert::keyExists($normalizedProperty, 'value');
        Assert::boolean($normalizedProperty['value']);

        return self::fromBoolean($normalizedProperty['value']);
    }

    /**
     * @return array<string, boolean|string>
     */
    public function normalize(): array
    {
        return [
            'type' => self::type(),
            'value' => $this->value,
        ];
    }
}
