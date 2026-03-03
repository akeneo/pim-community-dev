<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type FamilyOperator 'IN'|'NOT IN'|'EMPTY'|'NOT EMPTY'
 * @phpstan-type FamilyNormalized array{type: 'family', operator: FamilyOperator, value?: string[]}
 */
final class Family implements ConditionInterface
{
    /**
     * @param FamilyOperator $operator
     * @param string[]|null $value
     */
    private function __construct(
        private readonly string $operator,
        private readonly ?array $value = null,
    ) {
    }

    /**
     * @return 'family'
     */
    public static function type(): string
    {
        return 'family';
    }

    /**
     * @param array<string, mixed> $normalizedProperty
     */
    public static function fromNormalized(array $normalizedProperty): self
    {
        Assert::eq($normalizedProperty['type'], self::type());
        Assert::keyExists($normalizedProperty, 'operator');
        Assert::string($normalizedProperty['operator']);
        Assert::oneOf($normalizedProperty['operator'], ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY']);
        if (\in_array($normalizedProperty['operator'], ['IN', 'NOT IN'])) {
            Assert::keyExists($normalizedProperty, 'value');
            Assert::isArray($normalizedProperty['value']);
            Assert::allStringNotEmpty($normalizedProperty['value']);
            Assert::minCount($normalizedProperty['value'], 1);

            return new self($normalizedProperty['operator'], $normalizedProperty['value']);
        }

        Assert::keyNotExists($normalizedProperty, 'value');

        return new self($normalizedProperty['operator']);
    }

    /**
     * @return FamilyNormalized
     */
    public function normalize(): array
    {
        return \array_filter([
            'type' => self::type(),
            'operator' => $this->operator,
            'value' => $this->value,
        ]);
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
