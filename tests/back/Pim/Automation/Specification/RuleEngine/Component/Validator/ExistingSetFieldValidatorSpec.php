<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ExistingSetField;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ExistingSetFieldValidator;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterRegistryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ExistingSetFieldValidatorSpec extends ObjectBehavior
{
    function let(SetterRegistryInterface $setterRegistry, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($setterRegistry);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ExistingSetFieldValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['categories', new IsNull()]);
    }

    function it_does_not_validate_a_non_string_value(
        SetterRegistryInterface $setterRegistry,
        ExecutionContextInterface $context
    ) {
        $setterRegistry->getSetter(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new ExistingSetField());
    }

    function it_does_not_add_a_violation_if_a_setter_exists(
        SetterRegistryInterface $setterRegistry,
        ExecutionContextInterface $context,
        RemoverInterface $categoryRemover
    ) {
        $setterRegistry->getSetter('categories')->shouldBeCalled()->willReturn($categoryRemover);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('categories', new ExistingSetField());
    }

    function it_builds_a_violation_if_no_setter_exists_for_the_field(
        SetterRegistryInterface $setterRegistry,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ExistingSetField();
        $setterRegistry->getSetter('foo')->shouldBeCalled()->willReturn(null);
        $context->buildViolation($constraint->message, ['{{ field }}' => 'foo'])
                ->shouldBeCalled()
                ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('foo', $constraint);
    }
}
