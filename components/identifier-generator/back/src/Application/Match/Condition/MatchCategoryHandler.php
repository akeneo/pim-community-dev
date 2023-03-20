<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Category;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\CategoryOperator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\CategoriesHaveAtLeastOneChild;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MatchCategoryHandler implements MatchConditionHandler
{
    public function __construct(
        private readonly CategoriesHaveAtLeastOneChild $categoriesHaveAtLeastOneChild,
    ) {
    }

    public function getConditionClass(): string
    {
        return Category::class;
    }

    public function __invoke(ConditionInterface $condition, ProductProjection $productProjection): bool
    {
        Assert::isInstanceOf($condition, Category::class);

        return match ($condition->operator()) {
            CategoryOperator::IN => null !== $productProjection->categoryCodes() && [] !== \array_intersect($productProjection->categoryCodes(), (array) $condition->value()),
            CategoryOperator::NOT_IN => null !== $productProjection->categoryCodes() && [] === \array_intersect($productProjection->categoryCodes(), (array) $condition->value()),
            CategoryOperator::CLASSIFIED => !empty($productProjection->categoryCodes()),
            CategoryOperator::UNCLASSIFIED => empty($productProjection->categoryCodes()),
            CategoryOperator::IN_CHILDREN_LIST => $this->categoriesHaveAtLeastOneChild->among((array) $condition->value(), $productProjection->categoryCodes()),
            CategoryOperator::NOT_IN_CHILDREN_LIST => !$this->categoriesHaveAtLeastOneChild->among((array) $condition->value(), $productProjection->categoryCodes()),
        };
    }
}
