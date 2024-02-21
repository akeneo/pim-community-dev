<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SimpleSelectPropertyShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SimpleSelectPropertyShouldBeValidValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleSelectPropertyShouldBeValidValidatorSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $globalValidator,
        ExecutionContext $context,
        ValidatorInterface $validator
    ): void
    {
        $this->beConstructedWith($globalValidator);
        $this->initialize($context);

        $globalValidator->inContext($context)->willReturn($validator);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SimpleSelectPropertyShouldBeValidValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [['type' => 'simple_select', 'process' => ['type' => 'no']], new NotBlank()]);
    }

    public function it_should_not_validate_something_else_than_an_array(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new SimpleSelectPropertyShouldBeValid());
    }

    public function it_should_not_validate_a_property_which_have_no_type(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(['process' => []], new SimpleSelectPropertyShouldBeValid());
    }

    public function it_should_not_validate_a_property_which_have_bad_type(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(['type' => 'auto_number', 'process' => []], new SimpleSelectPropertyShouldBeValid());
    }

    public function it_should_build_violation_when_process_is_missing(ExecutionContext $context): void
    {
        $context
            ->buildViolation(
                'validation.identifier_generator.simple_select_property_fields_required',
                ['{{ field }}' => 'process']
            )
            ->shouldBeCalledOnce();

        $this->validate(['type' => 'simple_select', 'attributeCode' => 'color'], new SimpleSelectPropertyShouldBeValid());
    }

    public function it_should_build_violation_when_attribute_code_is_missing(ExecutionContext $context): void
    {
        $context
            ->buildViolation(
                'validation.identifier_generator.simple_select_property_fields_required',
                ['{{ field }}' => 'attributeCode']
            )
            ->shouldBeCalledOnce();

        $this->validate(['type' => 'simple_select', 'process' => ['type' => Process::PROCESS_TYPE_NO]], new SimpleSelectPropertyShouldBeValid());
    }

    public function it_should_validate_a_property_with_the_correct_parameters(ExecutionContext $context, ValidatorInterface $validator): void
    {
        $process = ['type' => Process::PROCESS_TYPE_NO];
        $structure = ['type' => 'simple_select', 'attributeCode' => 'color', 'process' => $process];

        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $validator->validate($structure, Argument::any())->shouldBeCalledOnce();

        $this->validate($structure, new SimpleSelectPropertyShouldBeValid());
    }
}
