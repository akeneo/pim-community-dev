<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Category;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\CategoriesHaveAtLeastOneChild;
use PhpSpec\ObjectBehavior;

class MatchCategoryHandlerSpec extends ObjectBehavior
{
    public function let(
        CategoriesHaveAtLeastOneChild $categoriesHaveAtLeastOneChild
    ) {
        $this->beConstructedWith($categoriesHaveAtLeastOneChild);
    }

    public function it_should_support_only_category_conditions(): void
    {
        $this->getConditionClass()->shouldReturn(Category::class);
    }

    public function it_should_throw_exception_when_invoked_with_something_else_than_category_condition(): void
    {
        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [
                Enabled::fromBoolean(true),
                new ProductProjection(true, null, [], []),
            ]);
    }

    public function it_should_match_classified(): void
    {
        $condition = Category::fromNormalized([
            'type' => 'category',
            'operator' => 'CLASSIFIED',
        ]);
        $this->__invoke($condition, new ProductProjection(true, null, [], []))->shouldReturn(false);
        $this->__invoke($condition, new ProductProjection(true, null, [], ['pants']))->shouldReturn(true);
        $this->__invoke($condition, new ProductProjection(true, null, [], ['pants', 'shoes']))->shouldReturn(true);
    }

    public function it_should_match_unclassified(): void
    {
        $condition = Category::fromNormalized([
            'type' => 'category',
            'operator' => 'UNCLASSIFIED',
        ]);
        $this->__invoke($condition, new ProductProjection(true, null, [], []))->shouldReturn(true);
        $this->__invoke($condition, new ProductProjection(true, null, [], ['pants']))->shouldReturn(false);
        $this->__invoke($condition, new ProductProjection(true, null, [], ['pants', 'shoes']))->shouldReturn(false);
    }

    public function it_should_match_in_list(): void
    {
        $condition = Category::fromNormalized([
            'type' => 'category',
            'operator' => 'IN',
            'value' => ['shoes']
        ]);
        $this->__invoke($condition, new ProductProjection(true, null, [], []))->shouldReturn(false);
        $this->__invoke($condition, new ProductProjection(true, null, [], ['pants']))->shouldReturn(false);
        $this->__invoke($condition, new ProductProjection(true, null, [], ['pants', 'shoes']))->shouldReturn(true);
    }

    public function it_should_match_not_in_list(): void
    {
        $condition = Category::fromNormalized([
            'type' => 'category',
            'operator' => 'NOT IN',
            'value' => ['shoes']
        ]);
        $this->__invoke($condition, new ProductProjection(true, null, [], []))->shouldReturn(true);
        $this->__invoke($condition, new ProductProjection(true, null, [], ['pants']))->shouldReturn(true);
        $this->__invoke($condition, new ProductProjection(true, null, [], ['pants', 'shoes']))->shouldReturn(false);
    }

    public function it_should_match_in_children(
        CategoriesHaveAtLeastOneChild $categoriesHaveAtLeastOneChild
    ): void {
        $condition = Category::fromNormalized([
            'type' => 'category',
            'operator' => 'IN CHILDREN',
            'value' => ['shoes']
        ]);
        $categoriesHaveAtLeastOneChild->among(['shoes'], [])->shouldBeCalled()->willReturn(false);
        $this->__invoke($condition, new ProductProjection(true, null, [], []))->shouldReturn(false);
        $categoriesHaveAtLeastOneChild->among(['shoes'], ['blue_shoes'])->shouldBeCalled()->willReturn(true);
        $this->__invoke($condition, new ProductProjection(true, null, [], ['blue_shoes']))->shouldReturn(true);
        $categoriesHaveAtLeastOneChild->among(['shoes'], ['pants'])->shouldBeCalled()->willReturn(false);
        $this->__invoke($condition, new ProductProjection(true, null, [], ['pants']))->shouldReturn(false);
    }

    public function it_should_match_not_in_children(
        CategoriesHaveAtLeastOneChild $categoriesHaveAtLeastOneChild
    ): void {
        $condition = Category::fromNormalized([
            'type' => 'category',
            'operator' => 'NOT IN CHILDREN',
            'value' => ['shoes']
        ]);
        $categoriesHaveAtLeastOneChild->among(['shoes'], [])->shouldBeCalled()->willReturn(false);
        $this->__invoke($condition, new ProductProjection(true, null, [], []))->shouldReturn(true);
        $categoriesHaveAtLeastOneChild->among(['shoes'], ['blue_shoes'])->shouldBeCalled()->willReturn(true);
        $this->__invoke($condition, new ProductProjection(true, null, [], ['blue_shoes']))->shouldReturn(false);
        $categoriesHaveAtLeastOneChild->among(['shoes'], ['pants'])->shouldBeCalled()->willReturn(false);
        $this->__invoke($condition, new ProductProjection(true, null, [], ['pants']))->shouldReturn(true);
    }
}
