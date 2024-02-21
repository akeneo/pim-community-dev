<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Family;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MatchFamilyHandler implements MatchConditionHandler
{
    public function getConditionClass(): string
    {
        return Family::class;
    }
    public function __invoke(ConditionInterface $condition, ProductProjection $productProjection): bool
    {
        Assert::isInstanceOf($condition, Family::class);

        return match ($condition->operator()) {
            'IN' => \in_array($productProjection->familyCode(), (array) $condition->value()),
            'NOT IN' => null !== $productProjection->familyCode()
                && !\in_array($productProjection->familyCode(), (array) $condition->value()),
            'EMPTY' => null === $productProjection->familyCode(),
            default => null !== $productProjection->familyCode(),
        };
    }
}
