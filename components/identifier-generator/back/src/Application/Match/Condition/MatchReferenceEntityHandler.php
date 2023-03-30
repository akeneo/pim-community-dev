<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ReferenceEntity;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MatchReferenceEntityHandler implements MatchConditionHandler
{
    public function getConditionClass(): string
    {
        return ReferenceEntity::class;
    }
    public function __invoke(ConditionInterface $condition, ProductProjection $productProjection): bool
    {
        Assert::isInstanceOf($condition, ReferenceEntity::class);

        $value = $productProjection->value($condition->attributeCode(), $condition->locale(), $condition->scope());
        /** @phpstan-ignore-next-line */
        if (null !== $value && ((string) $value === '')) {
            return false;
        }

        return null !== $value;
    }
}
