<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\MultiSelect;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MatchMultiSelectHandler implements MatchConditionHandler
{
    public function getConditionClass(): string
    {
        return MultiSelect::class;
    }
    public function __invoke(ConditionInterface $condition, ProductProjection $productProjection): bool
    {
        Assert::isInstanceOf($condition, MultiSelect::class);

        $value = $productProjection->value($condition->attributeCode(), $condition->locale(), $condition->scope());
        if (null !== $value && !\is_array($value)) {
            return false;
        }

        return match ($condition->operator()) {
            'IN' => null !== $value && [] !== \array_intersect((array) $value, (array) $condition->value()),
            'NOT IN' => null !== $value && [] === \array_intersect((array) $value, (array) $condition->value()),
            'EMPTY' => null === $value,
            'NOT EMPTY' => null !== $value
        };
    }
}
