<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ExecutionContextInterface;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Validator\Constraints\SingleIdentifierAttribute;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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
            ->addViolationAt(Argument::cetera())
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
            ->addViolationAt(Argument::cetera())
            ->shouldNotBeCalled();

         $this->validate($attribute, $constraint);
    }

    function it_adds_a_violation_if_attribute_identifier_already_exists(
        $context,
        $attributeRepository,
        AttributeInterface $attribute,
        AttributeInterface $identifier,
        SingleIdentifierAttribute $constraint
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_identifier');
        $attribute->getId()->willReturn(2);

        $attributeRepository->getIdentifier()->willReturn($identifier);

        $identifier->getId()->willReturn(1);

        $context
            ->addViolationAt('attributeType', $constraint->message)
            ->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }
}
