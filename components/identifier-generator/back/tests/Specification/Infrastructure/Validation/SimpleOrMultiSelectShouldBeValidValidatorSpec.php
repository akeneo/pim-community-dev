<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SimpleOrMultiSelectShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SimpleOrMultiSelectShouldBeValidValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleOrMultiSelectShouldBeValidValidatorSpec extends ObjectBehavior
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
        $this->shouldHaveType(SimpleOrMultiSelectShouldBeValidValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [
                ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'],
                new NotBlank()
            ]);
    }

    public function it_should_not_validate_if_condition_is_not_an_array(
        ValidatorInterface $validator,
    ): void {
        $validator->validate(Argument::any())->shouldNotBeCalled();
        $this->validate('foo', new SimpleOrMultiSelectShouldBeValid());
    }

    public function it_should_not_validate_other_conditions(
        ValidatorInterface $validator,
    ): void {
        $validator->validate(Argument::any())->shouldNotBeCalled();
        $this->validate(['type' => 'foo'], new SimpleOrMultiSelectShouldBeValid());
    }

    public function it_should_only_validate_condition_keys_without_operator(
        ValidatorInterface $validator,
    ): void {
        $condition = ['type' => 'simple_select', 'attributeCode' => 'color'];

        $validator->validate($condition, Argument::any())->shouldBeCalledTimes(1);
        $this->validate($condition, new SimpleOrMultiSelectShouldBeValid());
    }

    public function it_should_validate_condition_keys_without_value(
        ValidatorInterface $validator,
    ): void {
        $condition = ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'];

        $validator->validate($condition, Argument::any())->shouldBeCalledTimes(2);
        $this->validate($condition, new SimpleOrMultiSelectShouldBeValid());
    }

    public function it_should_validate_condition_keys_with_value_and_families(
        ValidatorInterface $validator,
        ExecutionContext $context,
    ): void {
        $condition = ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN', 'value' => ['green', 'red']];

        $validator->validate($condition, Argument::any())->shouldBeCalledTimes(2);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($condition, new SimpleOrMultiSelectShouldBeValid());
    }

    public function it_should_validate_multi_select(
        ValidatorInterface $validator,
        ExecutionContext $context,
    ): void {
        $condition = ['type' => 'multi_select', 'operator' => 'EMPTY', 'value' => ['option_a', 'option_b']];

        $validator->validate($condition, Argument::any())->shouldBeCalledTimes(2);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($condition, new SimpleOrMultiSelectShouldBeValid());
    }
}
