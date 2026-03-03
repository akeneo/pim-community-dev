<?php

namespace Specification\Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Validator\Constraints\SingleIdentifierAttributeValidator;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\SingleIdentifierAttribute;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class SingleIdentifierAttributeValidatorSpec extends ObjectBehavior
{
    public function let(AttributeRepositoryInterface $attributeRepository, ExecutionContextInterface $context): void
    {
        $this->beConstructedWith($attributeRepository);
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SingleIdentifierAttributeValidator::class);
    }

    public function it_does_nothing_if_attribute_type_is_not_identifier(
        $context,
        AttributeInterface $attribute,
        SingleIdentifierAttribute $constraint
    ): void {
        $attribute->getType()->willReturn('pim_catalog_text');

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    public function it_does_nothing_if_identifiers_id_are_the_same(
        $context,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute,
        AttributeInterface $identifier,
        SingleIdentifierAttribute $constraint
    ): void {
        $attribute->getType()->willReturn('pim_catalog_identifier');
        $attribute->getId()->willReturn(1);

        $attributeRepository->getAttributeCodesByType(AttributeTypes::IDENTIFIER)->willReturn(['some_code']);
        $attributeRepository->getIdentifier()->willReturn($identifier);

        $identifier->getId()->willReturn(1);

        $context
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($attribute, $constraint);
    }

    public function it_adds_a_violation_if_attribute_identifier_already_exists(
        $context,
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $attribute,
        AttributeInterface $identifier,
        SingleIdentifierAttribute $constraint,
        ConstraintViolationBuilderInterface $violationBuilder
    ): void {
        $attribute->getType()->willReturn('pim_catalog_identifier');
        $attribute->getId()->willReturn(2);

        $attributeRepository->getAttributeCodesByType(AttributeTypes::IDENTIFIER)->willReturn(['some_code']);
        $attributeRepository->getIdentifier()->willReturn($identifier);

        $identifier->getId()->willReturn(1);

        $context
            ->buildViolation($constraint->message)
            ->shouldBeCalled()
            ->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($attribute, $constraint);
    }
}
