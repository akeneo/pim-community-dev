<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\IsTableAttributeConfigured;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\IsTableAttributeConfiguredValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class IsTableAttributeConfiguredValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContext $context)
    {
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(IsTableAttributeConfiguredValidator::class);
    }

    function it_throws_an_exception_when_provided_with_an_invalid_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [new Attribute(), new NotBlank()]);
    }

    function it_can_only_validate_an_attribute()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [new \stdClass(), new IsTableAttributeConfigured()]
        );
    }

    function it_does_not_build_any_violation_for_a_non_table_attribute_with_no_configuration(
        ExecutionContext $context,
        AttributeInterface $name
    ) {
        $name->getType()->willReturn(AttributeTypes::TEXT);
        $name->getRawTableConfiguration()->willReturn(null);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($name, new IsTableAttributeConfigured());
    }

    function it_does_not_build_any_violation_for_a_configured_table_attribute(
        ExecutionContext $context,
        AttributeInterface $nutrition
    ) {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getRawTableConfiguration()->willReturn(['proper_configuration']);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($nutrition, new IsTableAttributeConfigured());
    }

    function it_builds_a_violation_for_a_non_table_attribute_with_configuration(
        ExecutionContext $context,
        ConstraintViolationBuilder $violationBuilder,
        AttributeInterface $name
    ) {
        $name->getType()->willReturn(AttributeTypes::TEXT);
        $name->getRawTableConfiguration()->willReturn(['configuration']);

        $context->buildViolation('pim_table_configuration.validation.table_configuration.must_not_be_filled', [])
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->setParameter('%type%', 'pim_catalog_text')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('table_configuration')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($name, new IsTableAttributeConfigured());
    }

    function it_builds_a_violation_for_a_non_configured_table_attribute(
        ExecutionContext $context,
        ConstraintViolationBuilder $violationBuilder,
        AttributeInterface $nutrition
    ) {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getRawTableConfiguration()->willReturn(null);

        $context->buildViolation('pim_table_configuration.validation.table_configuration.must_be_filled', [])
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->atPath('table_configuration')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($nutrition, new IsTableAttributeConfigured());
    }
}
