<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;

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
        /** @phpstan-ignore-next-line */
        private readonly string $operator,
        /** @phpstan-ignore-next-line */
        private readonly ?array $value = null,
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
        // TODO: Implement normalize() method.
        /** @phpstan-ignore-next-line */
        return new self('');
    }

    public function normalize(): array
    {
        // TODO: Implement normalize() method.
        /** @phpstan-ignore-next-line */
        return [];
    }

    public function match(ProductProjection $productProjection): bool
    {
        // TODO: Implement match() method.
        return false;
    }
}
