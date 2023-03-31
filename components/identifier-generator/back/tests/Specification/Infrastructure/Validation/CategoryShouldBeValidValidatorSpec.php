<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\CategoryShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\CategoryShouldBeValidValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryShouldBeValidValidatorSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $globalValidator,
        ExecutionContext $context,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith($globalValidator);
        $this->initialize($context);

        $globalValidator->inContext($context)->willReturn($validator);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(CategoryShouldBeValidValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [['type' => 'category', 'operator' => 'IN', 'value' => ['shirts']], new NotBlank()]);
    }

    public function it_should_not_validate_if_condition_is_not_an_array(
        ValidatorInterface $validator,
    ): void {
        $condition = 'foo';
        $validator->validate(Argument::any())->shouldNotBeCalled();
        $this->validate($condition, new CategoryShouldBeValid());
    }

    public function it_should_not_validate_other_conditions(
        ValidatorInterface $validator,
    ): void {
        $condition = ['type' => 'foo'];

        $validator->validate(Argument::any())->shouldNotBeCalled();
        $this->validate($condition, new CategoryShouldBeValid());
    }

    public function it_should_only_validate_condition_keys(
        ValidatorInterface $validator,
    ): void {
        $condition = ['type' => 'category', 'foo' => 'bar'];

        $validator->validate($condition, Argument::any())->shouldBeCalledTimes(1);
        $this->validate($condition, new CategoryShouldBeValid());
    }

    public function it_should_validate_condition_keys_without_value(
        ValidatorInterface $validator,
    ): void {
        $condition = ['type' => 'category', 'operator' => 'CLASSIFIED'];

        $validator->validate($condition, Argument::any())->shouldBeCalledTimes(2);
        $this->validate($condition, new CategoryShouldBeValid());
    }

    public function it_should_validate_condition_keys_with_value_and_categories(
        ValidatorInterface $validator,
        ExecutionContext $context,
    ): void {
        $condition = ['type' => 'category', 'operator' => 'IN', 'value' => ['shirts']];

        $validator->validate($condition, Argument::any())->shouldBeCalledTimes(2);
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate($condition, new CategoryShouldBeValid());
    }
}
