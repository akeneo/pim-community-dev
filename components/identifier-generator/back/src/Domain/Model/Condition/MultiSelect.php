<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type MultiSelectOperator 'IN'|'NOT IN'|'EMPTY'|'NOT EMPTY'
 * @phpstan-type MultiSelectNormalized array{
 *   type: 'multi_select',
 *   operator: MultiSelectOperator,
 *   attributeCode: string,
 *   value?: string[],
 *   scope?: string,
 *   locale?: string,
 * }
 */
final class MultiSelect implements ConditionInterface
{
    /**
     * @param MultiSelectOperator $operator
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
     * @return 'multi_select'
     */
    public static function type(): string
    {
        return 'multi_select';
    }

    /**
     * @param array<string, mixed> $normalizedProperty
     */
    public static function fromNormalized(array $normalizedProperty): self
    {
        Assert::eq($normalizedProperty['type'], self::type());
        Assert::keyExists($normalizedProperty, 'attributeCode');
        Assert::stringNotEmpty($normalizedProperty['attributeCode']);

        Assert::nullOrString($normalizedProperty['scope'] ?? null);
        Assert::nullOrString($normalizedProperty['locale'] ?? null);

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

    /**
     * @return MultiSelectNormalized
     */
    public function normalize(): array
    {
        return \array_filter([
            'type' => self::type(),
            'attributeCode' => $this->attributeCode,
            'operator' => $this->operator,
            'value' => $this->value,
            'scope' => $this->scope,
            'locale' => $this->locale,
        ], fn (mixed $var): bool => null !== $var);
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    public function locale(): ?string
    {
        return $this->locale;
    }

    public function scope(): ?string
    {
        return $this->scope;
    }

    /**
     * @return MultiSelectOperator
     */
    public function operator(): string
    {
        return $this->operator;
    }

    /**
     * @return string[]|null
     */
    public function value(): ?array
    {
        return $this->value;
    }
}
