<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ExistingRemoveField;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ExistingRemoveFieldValidator;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverRegistryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ExistingRemoveFieldValidatorSpec extends ObjectBehavior
{
    function let(RemoverRegistryInterface $removerRegistry, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($removerRegistry);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ExistingRemoveFieldValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['categories', new IsNull()]);
    }

    function it_does_not_validate_a_non_string_value(
        RemoverRegistryInterface $removerRegistry,
        ExecutionContextInterface $context
    ) {
        $removerRegistry->getRemover(Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new ExistingRemoveField());
    }

    function it_does_not_add_a_violation_if_a_remover_exists(
        RemoverRegistryInterface $removerRegistry,
        ExecutionContextInterface $context,
        RemoverInterface $categoryRemover
    ) {
        $removerRegistry->getRemover('categories')->shouldBeCalled()->willReturn($categoryRemover);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('categories', new ExistingRemoveField());
    }

    function it_builds_a_violation_if_no_remover_exists_for_the_field(
        RemoverRegistryInterface $removerRegistry,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ExistingRemoveField();
        $removerRegistry->getRemover('foo')->shouldBeCalled()->willReturn(null);
        $context->buildViolation($constraint->message, ['%field%' => 'foo'])
                ->shouldBeCalled()
                ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate('foo', $constraint);
    }
}
