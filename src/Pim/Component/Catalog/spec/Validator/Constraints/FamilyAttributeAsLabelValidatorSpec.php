<?php

namespace spec\Pim\Component\Catalog\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Validator\Constraints\FamilyAttributeAsLabel;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FamilyAttributeAsLabelValidatorSpec extends ObjectBehavior
{
    function let(FamilyAttributeAsLabel $minimumRequirements, ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement('Symfony\Component\Validator\ConstraintValidatorInterface');
    }

    function it_validates_family(
        $minimumRequirements,
        $context,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ConstraintViolationBuilderInterface $violation
    ) {
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('attributeAsLabelCode');
        $family->getAttributeCodes()->willReturn(['attributeAsLabelCode', 'anotherAttribute']);
        $attributeAsLabel->getType()->willReturn('pim_catalog_text');

        $this->validate($family, $minimumRequirements);
        $context->buildViolation(Argument::cetera())->willReturn($violation)->shouldNotBeCalled();
    }

    function it_invalidates_family_when_attribute_as_label_is_not_present(
        $minimumRequirements,
        $context,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ConstraintViolationBuilderInterface $violation
    ) {
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('attributeAsLabelCode');
        $family->getAttributeCodes()->willReturn(['anotherAttribute']);
        $attributeAsLabel->getType()->willReturn('pim_catalog_text');

        $context->buildViolation(Argument::any())->willReturn($violation)->shouldBeCalled();
        $violation->atPath(Argument::any())->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($family, $minimumRequirements);
    }

    function it_invalidates_family_when_attribute_is_not_text_type(
        $minimumRequirements,
        $context,
        FamilyInterface $family,
        AttributeInterface $attributeAsLabel,
        ConstraintViolationBuilderInterface $violation
    ) {
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $attributeAsLabel->getCode()->willReturn('attributeAsLabelCode');
        $family->getAttributeCodes()->willReturn(['attributeAsLabelCode', 'anotherAttribute']);
        $attributeAsLabel->getType()->willReturn('wrong_type');

        $context->buildViolation(Argument::any())->willReturn($violation)->shouldBeCalled();
        $violation->atPath(Argument::any())->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($family, $minimumRequirements);
    }
}
