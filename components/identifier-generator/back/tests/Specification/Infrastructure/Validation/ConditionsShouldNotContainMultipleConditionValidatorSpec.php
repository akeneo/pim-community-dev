<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\ConditionsShouldNotContainMultipleCondition;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\ConditionsShouldNotContainMultipleConditionValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConditionsShouldNotContainMultipleConditionValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContext $context): void
    {
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ConditionsShouldNotContainMultipleConditionValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [[], new NotBlank()]);
    }

    public function it_should_not_validate_something_else_than_an_array(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new ConditionsShouldNotContainMultipleCondition(['enabled', 'family']));
    }

    public function it_should_not_validate_something_else_than_an_array_of_array(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate([new \stdClass()], new ConditionsShouldNotContainMultipleCondition(['enabled', 'family']));
    }

    public function it_should_not_validate_something_else_than_an_array_of_property(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate([[]], new ConditionsShouldNotContainMultipleCondition(['enabled', 'family']));
    }

    public function it_should_not_validate_conditions_without_enabled(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $conditions = [
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['type' => 'free_text', 'string' => 'ghijkl'],
        ];
        $this->validate($conditions, new ConditionsShouldNotContainMultipleCondition(['enabled', 'family']));
    }

    public function it_should_build_violation_when_conditions_contains_multiple_enabled(ExecutionContext $context): void
    {
        $conditions = [
            ['type' => 'enabled', 'value' => true],
            ['type' => 'enabled', 'value' => false],
        ];

        $context
            ->buildViolation('validation.identifier_generator.conditions_limit_reached', [
                '{{limit}}' => 1,
                '{{type}}' => 'enabled',
            ])
            ->shouldBeCalled();

        $this->validate($conditions, new ConditionsShouldNotContainMultipleCondition(['enabled', 'family']));
    }

    public function it_should_be_valid_when_enabled_is_under_limit(ExecutionContext $context): void
    {
        $conditions = [
            ['type' => 'family', 'IN' => ['shirts']],
            ['type' => 'enabled', 'numberMin' => 3, 'digitsMin' => 2],
        ];

        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate($conditions, new ConditionsShouldNotContainMultipleCondition(['enabled', 'family']));
    }
}
