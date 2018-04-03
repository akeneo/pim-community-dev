<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\CategoryInterface;
use Pim\Component\Catalog\Validator\Constraints\IsRootCategory;
use Pim\Component\Catalog\Validator\Constraints\IsRootCategoryValidator;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsRootCategoryValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(IsRootCategoryValidator::class);
    }

    function it_is_a_validator_constraint()
    {
        $this->shouldBeAnInstanceOf(ConstraintValidator::class);
    }

    function it_does_not_add_violation_when_validating_null_value($context, IsRootCategory $constraint)
    {
        $context->buildViolation()->shouldNotBeCalled();
        $this->validate(null, $constraint);
    }

    function it_does_not_add_violation_when_validating_something_else_than_a_category(
        $context,
        IsRootCategory $constraint
    ) {
        $context->buildViolation()->shouldNotBeCalled();

        $this->validate(new \stdClass(), $constraint);
    }

    function it_does_not_add_violation_when_validating_a_category_that_does_not_have_parent(
        $context,
        CategoryInterface $category,
        IsRootCategory $constraint
    ) {
        $category->getParent()->willReturn(null);
        $context->buildViolation()->shouldNotBeCalled();

        $this->validate($category, $constraint);
    }

    function it_adds_violation_when_validating_a_category_that_has_parent(
        $context,
        CategoryInterface $category,
        CategoryInterface $parent,
        IsRootCategory $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $category->getParent()->willReturn($parent);
        $category->getCode()->willReturn('not_root');

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation
            ->setParameter('%category%', 'not_root')
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->addViolation()->shouldBeCalled();

        $this->validate($category, $constraint);
    }
}


