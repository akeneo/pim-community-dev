<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints\AttributeGroups;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\AttributeGroups\MaxAttributeGroupCount;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class MaxAttributeGroupCountValidatorSpec extends ObjectBehavior
{
    function let(
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        ExecutionContext $executionContext
    ) {
        $this->beConstructedWith($attributeGroupRepository, 10);
        $this->initialize($executionContext);
    }

    function it_should_throw_an_exception_when_called_with_wrong_constraint(
        AttributeGroupInterface $attributeGroup
    ) {
        $this->shouldThrow(UnexpectedTypeException::class)->during('validate', [$attributeGroup, new Blank()]);
    }

    function it_should_only_validate_attribute_group(
        ExecutionContext $executionContext
    ) {
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new MaxAttributeGroupCount());
    }

    function it_should_build_violation_when_there_are_too_many_attribute_groups(
        AttributeGroupInterface $attributeGroup,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        ExecutionContext $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $attributeGroupRepository->countAll()->willReturn(20);
        $executionContext->buildViolation(Argument::cetera())->willReturn($violationBuilder)->shouldBeCalled();
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attributeGroup, new MaxAttributeGroupCount());
    }

    function it_should_not_build_violation_if_attribute_group_is_already_saved(
        AttributeGroupInterface $attributeGroup,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        ExecutionContext $executionContext
    ) {
        $attributeGroup->getId()->willReturn(10);
        $attributeGroupRepository->countAll()->willReturn(20);
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeGroup, new MaxAttributeGroupCount());
    }

    function it_should_not_build_violation_when_there_are_not_too_many_attribute_group(
        AttributeGroupInterface $attributeGroup,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        ExecutionContext $executionContext,
    ) {
        $attributeGroupRepository->countAll()->willReturn(9);
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attributeGroup, new MaxAttributeGroupCount());
    }
}
