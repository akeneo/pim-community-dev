<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ReferenceEntityOperator 'IN'|'NOT IN'|'EMPTY'|'NOT EMPTY'
 * @phpstan-type ReferenceEntityNormalized array{
 *   type: 'reference_entity',
 *   operator: ReferenceEntityOperator,
 *   attributeCode: string,
 *   value?: string[],
 *   scope?: string,
 *   locale?: string,
 * }
 */
final class ReferenceEntity implements ConditionInterface
{
    /**
     * @param ReferenceEntityOperator $operator
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
     * @return 'reference_entity'
     */
    public static function type(): string
    {
        return 'reference_entity';
    }

    /**
     * @param array<string, mixed> $normalizedCondition
     */
    public static function fromNormalized(array $normalizedCondition): self
    {
        Assert::eq($normalizedCondition['type'] ?? null, self::type());
        Assert::keyExists($normalizedCondition, 'attributeCode');
        Assert::stringNotEmpty($normalizedCondition['attributeCode']);

        Assert::nullOrNotEmptyString($normalizedCondition['scope'] ?? null);
        Assert::nullOrNotEmptyString($normalizedCondition['locale'] ?? null);

        Assert::keyExists($normalizedCondition, 'operator');
        Assert::string($normalizedCondition['operator']);
        Assert::same($normalizedCondition['operator'], 'NOT EMPTY');

        return new self(
            $normalizedCondition['operator'],
            $normalizedCondition['attributeCode'],
            null,
            $normalizedCondition['scope'] ?? null,
            $normalizedCondition['locale'] ?? null,
        );
    }

    /**
     * @return ReferenceEntityNormalized
     */
    public function normalize(): array
    {
        return \array_filter([
            'type' => self::type(),
            'attributeCode' => $this->attributeCode,
            'operator' => $this->operator,
            'scope' => $this->scope,
            'locale' => $this->locale,
        ], fn (mixed $var): bool => null !== $var);
    }

    public function match(ProductProjection $productProjection): bool
    {
        $value = $productProjection->value($this->attributeCode, $this->locale, $this->scope);
        if (null !== $value && !\is_string($value)) {
            return false;
        }

        return null !== $value;
    }
}
