<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Validator\Constraints\SingleIdentifierAttribute;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class SingleIdentifierAttributeValidatorSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($attributeRepository);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Validator\Constraints\SingleIdentifierAttributeValidator');
    }

    function it_does_nothing_if_attribute_type_is_not_identifier(
        $context,
        AttributeInterface $attribute,
        Constraint $constraint
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_text');

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_does_nothing_if_identifiers_id_are_the_same(
        $context,
        $attributeRepository,
        AttributeInterface $attribute,
        AttributeInterface $identifier,
        Constraint $constraint
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $attribute->getId()->willReturn(1);

        $attributeRepository->getIdentifier()->willReturn($identifier);

        $identifier->getId()->willReturn(1);

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    function it_adds_a_violation_if_attribute_identifier_already_exists(
        $context,
        $attributeRepository,
        AttributeInterface $attribute,
        AttributeInterface $identifier,
        SingleIdentifierAttribute $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $attribute->getId()->willReturn(2);

        $attributeRepository->getIdentifier()->willReturn($identifier);

        $identifier->getId()->willReturn(1);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->atPath('attributeType')->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }
}
