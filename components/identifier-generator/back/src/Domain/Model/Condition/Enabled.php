<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type EnabledNormalized array{type: string, value: bool}
 */
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
     * @param array<string, mixed> $normalizedProperty
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
     * @return EnabledNormalized
     */
    public function normalize(): array
    {
        return [
            'type' => self::type(),
            'value' => $this->value,
        ];
    }

    public function match(ProductProjection $productProjection): bool
    {
        return $productProjection->enabled() === $this->value;
    }
}
