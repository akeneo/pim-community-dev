<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ExistingClearField;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ExistingClearFieldValidator;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ExistingClearFieldValidatorSpec extends ObjectBehavior
{
    function let(ClearerRegistryInterface $clearerRegistry, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($clearerRegistry);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ExistingClearFieldValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['categories', new IsNull()]);
    }

    function it_does_not_validate_a_non_string_value(
        ClearerRegistryInterface $clearerRegistry,
        ExecutionContextInterface $context
    ) {
        $clearerRegistry->getClearer(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new ExistingClearField());
    }

    function it_does_not_add_a_violation_if_a_clearer_exists(
        ClearerRegistryInterface $clearerRegistry,
        ExecutionContextInterface $context,
        ClearerInterface $categoryClearer
    ) {
        $clearerRegistry->getClearer('categories')->shouldBeCalled()->willReturn($categoryClearer);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('categories', new ExistingClearField());
    }

    function it_builds_a_violation_if_no_clearer_exists_for_the_field(
        ClearerRegistryInterface $clearerRegistry,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ExistingClearField();
        $clearerRegistry->getClearer('foo')->shouldBeCalled()->willReturn(null);
        $context->buildViolation($constraint->message, ['{{ field }}' => 'foo'])
                ->shouldBeCalled()
                ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('foo', $constraint);
    }
}
