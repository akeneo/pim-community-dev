<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Webmozart\Assert\Assert;

/**
 * Condition allowing to apply the identifier generation
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Conditions
{
    /**
     * @param ConditionInterface[] $conditions
     */
    private function __construct(
        private array $conditions, // @phpstan-ignore-line
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
     * @param array<mixed> $normalizedConditions
     */
    public static function fromNormalized(array $normalizedConditions): self
    {
        // TODO
        return new self([]);
    }

    /**
     * @return array<string, string>
     */
    public function normalize(): array
    {
        return [];
    }
}
