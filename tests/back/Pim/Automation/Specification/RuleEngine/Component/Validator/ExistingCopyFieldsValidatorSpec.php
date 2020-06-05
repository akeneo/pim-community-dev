<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\CopyAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ExistingCopyFields;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ExistingCopyFieldsValidator;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierRegistryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ExistingCopyFieldsValidatorSpec extends ObjectBehavior
{
    function let(CopierRegistryInterface $copierRegistry, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($copierRegistry);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ExistingCopyFieldsValidator::class);
    }

    function it_throws_an_exception_for_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [new CopyAction([]), new IsNull()]);
    }

    function it_throw_an_exception_if_value_is_not_a_copy_action()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['test', new ExistingCopyFields()]);
    }

    function it_does_nothing_if_source_field_is_not_a_string(
        CopierRegistryInterface $copierRegistry,
        ExecutionContextInterface $context
    ) {
        $copierRegistry->getCopier(Argument::any(), Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new CopyAction(['from_field' => new \stdClass(), 'to_field' => 'name']),
            new ExistingCopyFields()
        );
    }

    function it_does_nothing_if_destination_field_is_not_a_string(
        CopierRegistryInterface $copierRegistry,
        ExecutionContextInterface $context
    ) {
        $copierRegistry->getCopier(Argument::any(), Argument::any())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new CopyAction(['from_field' => 'name', 'to_field' => false]),
            new ExistingCopyFields()
        );
    }

    function it_does_not_add_a_violation_if_a_copier_exists(
        CopierRegistryInterface $copierRegistry,
        ExecutionContextInterface $context,
        CopierInterface $categoryAdder
    ) {
        $copierRegistry->getCopier('name', 'description')->shouldBeCalled()->willReturn($categoryAdder);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new CopyAction(['from_field' => 'name', 'to_field' => 'description']), new ExistingCopyFields());
    }

    function it_builds_a_violation_if_no_copier_exists_for_the_fields(
        CopierRegistryInterface $copierRegistry,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new ExistingCopyFields();
        $copierRegistry->getCopier('foo', 'bar')->shouldBeCalled()->willReturn(null);
        $context->buildViolation($constraint->message, ['%fromField%' => 'foo', '%toField%' => 'bar'])
                ->shouldBeCalled()
                ->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(new CopyAction(['from_field' => 'foo', 'to_field' => 'bar']), $constraint);
    }
}
