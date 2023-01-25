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
 * @phpstan-import-type ConditionNormalized from ConditionInterface
 * @phpstan-type ConditionsNormalized list<ConditionNormalized>
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
                Family::type() => Family::fromNormalized($normalizedCondition),
                SimpleSelect::type() => SimpleSelect::fromNormalized($normalizedCondition),
                default => throw new \InvalidArgumentException(sprintf('The Condition type "%s" does not exist', $normalizedCondition['type'])),
            };
        }

        return self::fromArray($conditions);
    }

    /**
     * @return ConditionsNormalized
     */
    public function normalize(bool $withAuto): array
    {
        return \array_map(
            function (ConditionInterface $condition) use ($withAuto) {
                $normalizedCondition = $condition->normalize();
                if ($withAuto) {
                    $normalizedCondition['auto'] = $condition->isAuto();
                }
            },
            $this->conditions
        );
    }

    public function match(ProductProjection $productProjection): bool
    {
        return \array_reduce(
            $this->conditions,
            fn (bool $prev, $condition): bool => $prev && $condition->match($productProjection),
            true
        );
    }

    public function push(ConditionInterface $autoCondition): self
    {
        $conditions = $this->conditions;
        $conditions[] = $autoCondition;

        return new self($conditions);
    }

    public function normalizeForDatabase()
    {
        return \array_map(
            static fn (ConditionInterface $condition) => $condition->normalize(),
            $this->nonAuto()->conditions,
        );
    }

    public function nonAuto(): self
    {
        return new self(
            \array_filter(
                $this->conditions,
                static fn (ConditionInterface $condition) => !$condition->isAuto()
            )
        );
    }
}
