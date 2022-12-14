<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyShouldBeValidValidator;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyShouldBeValid;
use Akeneo\Platform\Component\Webhook\Context;
use PhpParser\Node\Arg;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyShouldBeValidValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContext $context): void
    {
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FamilyShouldBeValidValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [['type' => 'enabled', 'operator' => 'IN', 'value' => ['shirts']], new NotBlank()]);
    }

    public function it_should_not_build_violation_when_family_constraint_is_valid(
        ExecutionContext $context
    ): void {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();
        $this->validate(['type' => 'family', 'operator' => 'IN', 'value' => ['shirts']], new FamilyShouldBeValid());
    }

    public function it_should_not_validate_something_else_than_an_array(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new FamilyShouldBeValid());
    }

    public function it_should_not_validate_a_condition_which_is_not_a_family(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(['type' => 'something_else', 'operator' => 'IN', 'value' => ['shirts']], new FamilyShouldBeValid());
    }

    public function it_should_build_a_violation_when_value_is_filled_with_EMPTY(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder,
    ): void {
        $context->buildViolation(Argument::type('string'))->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('value')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(['type' => 'family', 'operator' => 'EMPTY', 'value' => ['shirts']], new FamilyShouldBeValid());
    }

    public function it_should_build_a_violation_when_value_is_filled_with_NOT_EMPTY(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder,
    ): void {
        $context->buildViolation(Argument::type('string'))->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('value')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(['type' => 'family', 'operator' => 'NOT EMPTY', 'value' => ['shirts']], new FamilyShouldBeValid());
    }

    public function it_should_build_a_violation_when_value_is_not_an_array(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder,
    ): void {
        $context->buildViolation(Argument::type('string'))->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('value')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(['type' => 'family', 'operator' => 'IN', 'value' => 'shirts'], new FamilyShouldBeValid());
    }

    public function it_should_build_a_violation_when_value_is_not_an_array_of_strings(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder,
    ): void {
        $context->buildViolation(Argument::type('string'))->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('value')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(['type' => 'family', 'operator' => 'IN', 'value' => [true]], new FamilyShouldBeValid());
    }

    public function it_should_build_violation_when_operator_is_unknown(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ): void
    {
        $context->buildViolation(Argument::type('string'))->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('operator')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->setParameters([
            '{{ value }}' => '"unknown"',
            '{{ choices }}' => '"IN", "NOT IN", "EMPTY", "NOT EMPTY"',
        ])->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(['type' => 'family', 'operator' => 'unknown', 'value' => ['shirts']], new FamilyShouldBeValid());
    }
}
