<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Webmozart\Assert\Assert;

/**
 * Condition allowing to apply the identifier generation
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type EnabledNormalized from Enabled
 * @phpstan-type ConditionsNormalized list<EnabledNormalized>
 */
final class Conditions
{
    /**
     * @param ConditionInterface[] $conditions
     */
    private function __construct(
        private array $conditions,
    ) {
    }

    /**
     * @param ConditionInterface[] $conditions
     * @return static
     */
    public static function fromArray(array $conditions): self
    {
        Assert::allIsInstanceOf($conditions, ConditionInterface::class);

        return new self($conditions);
    }

    /**
     * @param list<array<string, mixed>> $normalizedConditions
     */
    public static function fromNormalized(array $normalizedConditions): self
    {
        $conditions = [];
        foreach ($normalizedConditions as $normalizedCondition) {
            Assert::isMap($normalizedCondition);
            Assert::stringNotEmpty($normalizedCondition['type'] ?? null);
            $conditions[] = match ($normalizedCondition['type']) {
                Enabled::type() => Enabled::fromNormalized($normalizedCondition),
                default => throw new \InvalidArgumentException(sprintf('The type %s does not exist', $normalizedCondition['type'])),
            };
        }

        return self::fromArray($conditions);
    }

    /**
     * @return ConditionsNormalized
     */
    public function normalize(): array
    {
        return \array_map(static fn (ConditionInterface $condition) => $condition->normalize(), $this->conditions);
    }

    public function match(ProductProjection $productProjection): bool
    {
        return \array_reduce(
            $this->conditions,
            fn (bool $prev, $condition): bool => $prev && $condition->match($productProjection),
            true
        );
    }
}
