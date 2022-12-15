<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type FamilyNormalized array{type: string, operator: string, value?: string[]}
 */
final class Family implements ConditionInterface
{
    public static function type(): string
    {
        return 'family';
    }

    /**
     * @param array<string, mixed> $normalizedProperty
     */
    public static function fromNormalized(array $normalizedProperty): self
    {
        // TODO: CPM-861
        return new self();
    }

    /**
     * @return FamilyNormalized
     */
    public function normalize(): array
    {
        // TODO: CPM-861
        throw new \Exception('not implemented');
    }

    public function match(ProductProjection $productProjection): bool
    {
        // TODO: CPM-861
        throw new \Exception('not implemented');
    }
}
