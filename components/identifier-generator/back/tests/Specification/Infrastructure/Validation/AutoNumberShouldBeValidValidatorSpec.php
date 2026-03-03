<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\AutoNumberShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\AutoNumberShouldBeValidValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AutoNumberShouldBeValidValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContext $context): void
    {
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AutoNumberShouldBeValidValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [['type' => 'auto_number', 'numberMin' => 2, 'digitsMin' => 3], new NotBlank()]);
    }

    public function it_should_not_validate_something_else_than_an_array(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new AutoNumberShouldBeValid());
    }

    public function it_should_not_validate_a_property_which_is_not_an_auto_number(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(['type' => 'free_text', 'string' => 'abcdef'], new AutoNumberShouldBeValid());
    }

    public function it_should_build_violation_when_auto_number_is_invalid(ExecutionContext $context): void
    {
        $autoNumberWithoutField = [
            'type' => 'auto_number',
            'numberMin' => 2,
        ];

        $context->buildViolation(
            'validation.identifier_generator.auto_number_fields_required',
            [
                '{{field}}' => 'numberMin, digitsMin',
            ]
        )->shouldBeCalled();

        $this->validate($autoNumberWithoutField, new AutoNumberShouldBeValid());
    }

    public function it_should_build_violation_when_auto_number_is_valid(ExecutionContext $context): void
    {
        $autoNumberValid = [
            'type' => 'auto_number',
            'numberMin' => 2,
            'digitsMin' => 2,
        ];

        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate($autoNumberValid, new AutoNumberShouldBeValid());
    }
}
