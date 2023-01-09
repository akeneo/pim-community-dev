<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type SimpleSelectOperator 'IN'|'NOT IN'|'EMPTY'|'NOT EMPTY'
 * @phpstan-type SimpleSelectNormalized array{
 *   type: 'simple_select',
 *   operator: SimpleSelectOperator,
 *   attributeCode: string,
 *   value?: string[],
 *   scope?: string,
 *   locale?: string,
 * }
 */
final class SimpleSelect implements ConditionInterface
{
    /**
     * @param SimpleSelectOperator $operator
     * @param string[]|null $value
     */
    private function __construct(
        private readonly string $operator,
        private readonly string $attributeCode,
        private readonly ?array $value = null,
        private readonly ?string $scope = null,
        private readonly ?string $locale = null,
    ) {
    }

    /**
     * @return 'simple_select'
     */
    public static function type(): string
    {
        return 'simple_select';
    }

    /**
     * @param array<string, mixed> $normalizedProperty
     */
    /** @phpstan-ignore-next-line */
    public static function fromNormalized(array $normalizedProperty): self
    {
        Assert::eq($normalizedProperty['type'], self::type());
        Assert::keyExists($normalizedProperty, 'attributeCode');
        Assert::stringNotEmpty($normalizedProperty['attributeCode']);

        if (\array_key_exists('scope', $normalizedProperty)) {
            Assert::stringNotEmpty($normalizedProperty['scope']);
        }
        if (\array_key_exists('locale', $normalizedProperty)) {
            Assert::stringNotEmpty($normalizedProperty['locale']);
        }

        Assert::keyExists($normalizedProperty, 'operator');
        Assert::string($normalizedProperty['operator']);
        Assert::oneOf($normalizedProperty['operator'], ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']);
        if (\in_array($normalizedProperty['operator'], ['IN', 'NOT IN'])) {
            Assert::keyExists($normalizedProperty, 'value');
            Assert::isArray($normalizedProperty['value']);
            Assert::allStringNotEmpty($normalizedProperty['value']);
            Assert::minCount($normalizedProperty['value'], 1);

            return new self(
                $normalizedProperty['operator'],
                $normalizedProperty['attributeCode'],
                $normalizedProperty['value'],
                $normalizedProperty['scope'] ?? null,
                $normalizedProperty['locale'] ?? null,
            );
        }

        Assert::keyNotExists($normalizedProperty, 'value');

        return new self(
            $normalizedProperty['operator'],
            $normalizedProperty['attributeCode'],
            null,
            $normalizedProperty['scope'] ?? null,
            $normalizedProperty['locale'] ?? null,
        );
    }

    public function normalize(): array
    {
        return \array_filter([
            'type' => self::type(),
            'attributeCode' => $this->attributeCode,
            'operator' => $this->operator,
            'value' => $this->value,
            'scope' => $this->scope,
            'locale' => $this->locale,
        ]);
    }

    public function match(ProductProjection $productProjection): bool
    {
        $value = $productProjection->value($this->attributeCode, $this->locale, $this->scope);
        if (null !== $value && !\is_string($value)) {
            return false;
        }

        return match ($this->operator) {
            'IN' => null !== $value && \in_array($value, $this->value),
            'NOT IN' => null !== $value && !\in_array($value, $this->value),
            'EMPTY' => null === $value,
            default => null !== $value
        };
    }
}
