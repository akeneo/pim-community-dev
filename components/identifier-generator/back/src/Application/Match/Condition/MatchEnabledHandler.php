<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MatchEnabledHandler implements MatchConditionHandler
{
    public function getConditionClass(): string
    {
        return Enabled::class;
    }
    public function __invoke(ConditionInterface $condition, ProductProjection $productProjection): bool
    {
        Assert::isInstanceOf($condition, Enabled::class);

        return $productProjection->enabled() === $condition->value();
    }
}
