<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type CategoryNormalized array{type: 'category', operator: value-of<CategoryOperator>, value?: string[]}
 */
final class Category implements ConditionInterface
{
    /**
     * @param CategoryOperator $operator
     * @param string[]|null $value
     */
    private function __construct(
        private readonly CategoryOperator $operator,
        private readonly ?array $value = null,
    ) {
    }

    /**
     * @return 'category'
     */
    public static function type(): string
    {
        return 'category';
    }

    /**
     * @param array<string, mixed> $normalizedProperty
     */
    public static function fromNormalized(array $normalizedProperty): self
    {
        Assert::eq($normalizedProperty['type'], self::type());
        Assert::keyExists($normalizedProperty, 'operator');
        Assert::string($normalizedProperty['operator']);
        $operator = CategoryOperator::tryFrom($normalizedProperty['operator']);
        if (!$operator) {
            throw new \InvalidArgumentException(\sprintf('%s invalid operator for category', $normalizedProperty['operator']));
        }
        if (\in_array($operator->value, [CategoryOperator::CLASSIFIED->value, CategoryOperator::UNCLASSIFIED->value])) {
            Assert::keyNotExists($normalizedProperty, 'value');

            return new self($operator);
        }

        Assert::keyExists($normalizedProperty, 'value');
        Assert::isArray($normalizedProperty['value']);
        Assert::allStringNotEmpty($normalizedProperty['value']);
        Assert::minCount($normalizedProperty['value'], 1);

        return new self($operator, $normalizedProperty['value']);
    }

    public function normalize(): array
    {
        return \array_filter([
            'type' => self::type(),
            'operator' => $this->operator->value,
            'value' => $this->value,
        ], fn (mixed $var): bool => null !== $var);
    }

    public function operator(): CategoryOperator
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
