<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

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
     * @param array<string, mixed> $normalizedCondition
     */
    public static function fromNormalized(array $normalizedCondition): self
    {
        Assert::eq($normalizedCondition['type'], self::type());
        Assert::keyExists($normalizedCondition, 'attributeCode');
        Assert::stringNotEmpty($normalizedCondition['attributeCode']);

        Assert::nullOrString($normalizedCondition['scope'] ?? null);
        Assert::nullOrString($normalizedCondition['locale'] ?? null);

        Assert::keyExists($normalizedCondition, 'operator');
        Assert::string($normalizedCondition['operator']);
        Assert::oneOf($normalizedCondition['operator'], ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']);
        if (\in_array($normalizedCondition['operator'], ['IN', 'NOT IN'])) {
            Assert::keyExists($normalizedCondition, 'value');
            Assert::isArray($normalizedCondition['value']);
            Assert::allStringNotEmpty($normalizedCondition['value']);
            Assert::minCount($normalizedCondition['value'], 1);

            return new self(
                $normalizedCondition['operator'],
                $normalizedCondition['attributeCode'],
                $normalizedCondition['value'],
                $normalizedCondition['scope'] ?? null,
                $normalizedCondition['locale'] ?? null,
            );
        }

        Assert::keyNotExists($normalizedCondition, 'value');

        return new self(
            $normalizedCondition['operator'],
            $normalizedCondition['attributeCode'],
            null,
            $normalizedCondition['scope'] ?? null,
            $normalizedCondition['locale'] ?? null,
        );
    }

    /**
     * @return SimpleSelectNormalized
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
