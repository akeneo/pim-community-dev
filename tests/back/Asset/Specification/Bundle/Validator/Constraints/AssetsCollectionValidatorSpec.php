<?php

namespace Specification\Akeneo\Asset\Bundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Asset\Bundle\Validator\Constraints\AssetsCollectionConstraint;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AssetsCollectionValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_does_not_validate_if_object_is_not_an_attribute(
        $context,
        AssetsCollectionConstraint $constraint
    ) {
        $object = new \stdClass();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($object, $constraint);
    }

    function it_does_not_validate_if_attribute_is_not_assets_collection(
        $context,
        AssetsCollectionConstraint $constraint,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn('other_code');

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_if_attribute_is_localizable(
        $context,
        AssetsCollectionConstraint $constraint,
        AttributeInterface $attribute,
        ConstraintViolationBuilderInterface $violation
    ) {
        $attribute->getType()->willReturn('pim_assets_collection');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getCode()->willReturn('code');
        $violationData = [ '%attribute%' => 'code' ];

        $context->buildViolation($constraint->message, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_if_attribute_is_scopable(
        $context,
        AssetsCollectionConstraint $constraint,
        AttributeInterface $attribute,
        ConstraintViolationBuilderInterface $violation
    ) {
        $attribute->getType()->willReturn('pim_assets_collection');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getCode()->willReturn('code');
        $violationData = [ '%attribute%' => 'code' ];

        $context->buildViolation($constraint->message, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($attribute, $constraint);
    }

    function it_adds_violation_if_attribute_is_locale_specific(
        $context,
        AssetsCollectionConstraint $constraint,
        AttributeInterface $attribute,
        ConstraintViolationBuilderInterface $violation
    ) {
        $attribute->getType()->willReturn('pim_assets_collection');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(true);
        $attribute->getCode()->willReturn('code');
        $violationData = [ '%attribute%' => 'code' ];

        $context->buildViolation($constraint->message, $violationData)
            ->shouldBeCalled()
            ->willReturn($violation);

        $this->validate($attribute, $constraint);
    }

    function it_does_not_add_violation_if_attribute_is_valid(
        $context,
        AssetsCollectionConstraint $constraint,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn('pim_assets_collection');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);
        $attribute->getCode()->willReturn('code');

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }
}
