<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ExistingAddField;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ExistingAddFieldValidator;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderRegistryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ExistingAddFieldValidatorSpec extends ObjectBehavior
{
    function let(AdderRegistryInterface $adderRegistry, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($adderRegistry);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ExistingAddFieldValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['categories', new IsNull()]);
    }

    function it_does_not_validate_a_non_string_value(
        AdderRegistryInterface $adderRegistry,
        ExecutionContextInterface $context
    ) {
        $adderRegistry->getAdder(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new ExistingAddField());
    }

    function it_does_not_add_a_violation_if_an_adder_exists(
        AdderRegistryInterface $adderRegistry,
        ExecutionContextInterface $context,
        AdderInterface $categoryAdder
    ) {
        $adderRegistry->getAdder('categories')->shouldBeCalled()->willReturn($categoryAdder);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('categories', new ExistingAddField());
    }

    function it_builds_a_violation_if_no_adder_exists_for_the_field(
        AdderRegistryInterface $adderRegistry,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ExistingAddField();
        $adderRegistry->getAdder('foo')->shouldBeCalled()->willReturn(null);
        $context->buildViolation($constraint->message, ['{{ field }}' => 'foo'])
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('foo', $constraint);
    }
}
