<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\EnabledShouldBeValidValidator;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\EnabledShouldBeValid;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnabledShouldBeValidValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContext $context): void
    {
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(EnabledShouldBeValidValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [['type' => 'enabled', 'value' => 'abcdef'], new NotBlank()]);
    }

    public function it_should_not_validate_something_else_than_an_array(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new EnabledShouldBeValid());
    }

    public function it_should_not_validate_a_condition_which_is_not_a_enabled(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(['type' => 'something_else'], new EnabledShouldBeValid());
    }

    public function it_should_build_violation_when_value_is_missing(ExecutionContext $context): void
    {
        $enabledWithoutValue = [
            'type' => 'enabled',
        ];

        $context->buildViolation(
            'validation.identifier_generator.enabled_value_field_required'
        )->shouldBeCalled();

        $this->validate($enabledWithoutValue, new EnabledShouldBeValid());
    }

    public function it_should_build_violation_when_value_is_not_a_boolean(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ): void
    {
        $enabledWithoutValue = [
            'type' => 'enabled', 'value' => 'bar',
        ];

        $context->buildViolation(
            'validation.identifier_generator.enabled_boolean_value'
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('value')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate($enabledWithoutValue, new EnabledShouldBeValid());
    }

    public function it_should_not_build_violation_when_enabled_is_valid(ExecutionContext $context): void
    {
        $validEnabled = [
            'type' => 'enabled',
            'value' => true,
        ];

        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate($validEnabled, new EnabledShouldBeValid());
    }
}
