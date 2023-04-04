<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\SimpleSelect;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MatchSimpleSelectHandler implements MatchConditionHandler
{
    public function getConditionClass(): string
    {
        return SimpleSelect::class;
    }
    public function __invoke(ConditionInterface $condition, ProductProjection $productProjection): bool
    {
        Assert::isInstanceOf($condition, SimpleSelect::class);

        $value = $productProjection->value($condition->attributeCode(), $condition->locale(), $condition->scope());
        if (null !== $value && !\is_string($value)) {
            return false;
        }

        return match ($condition->operator()) {
            'IN' => null !== $value && \in_array($value, $condition->value() ?? []),
            'NOT IN' => null !== $value && !\in_array($value, $condition->value() ?? []),
            'EMPTY' => null === $value,
            default => null !== $value
        };
    }
}
