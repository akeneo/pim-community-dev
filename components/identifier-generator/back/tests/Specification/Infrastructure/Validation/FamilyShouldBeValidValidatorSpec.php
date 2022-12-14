<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyShouldBeValidValidator;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamilyCodes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyShouldBeValidValidatorSpec extends ObjectBehavior
{
    public function let(
        FindFamilyCodes $findFamilyCodes,
        ValidatorInterface $globalValidator,
        ExecutionContext $context,
        ValidatorInterface $validator
    ): void
    {
        $this->beConstructedWith($findFamilyCodes, $globalValidator);
        $this->initialize($context);

        $findFamilyCodes
            ->fromQuery(Argument::any())
            ->willReturn(['shirts']);

        $globalValidator->inContext($context)->willReturn($validator);
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

    public function it_should_not_validate_if_condition_is_not_an_array(
        ValidatorInterface $validator,
    ): void {
        $condition = 'foo';
        $validator->validate(Argument::any())->shouldNotBeCalled();
        $this->validate($condition, new FamilyShouldBeValid());
    }

    public function it_should_not_validate_other_conditions(
        ValidatorInterface $validator,
    ): void {
        $condition = ['type' => 'foo'];

        $validator->validate(Argument::any())->shouldNotBeCalled();
        $this->validate($condition, new FamilyShouldBeValid());
    }

    public function it_should_only_validate_condition_keys(
        ValidatorInterface $validator,
    ): void {
        $condition = ['type' => 'family', 'foo' => 'bar'];

        $validator->validate($condition, Argument::any())->shouldBeCalledTimes(1);
        $this->validate($condition, new FamilyShouldBeValid());
    }

    public function it_should_validate_condition_keys_without_value(
        ValidatorInterface $validator,
    ): void {
        $condition = ['type' => 'family', 'operator' => 'EMPTY'];

        $validator->validate($condition, Argument::any())->shouldBeCalledTimes(2);
        $this->validate($condition, new FamilyShouldBeValid());
    }

    public function it_should_validate_condition_keys_with_value_and_families(
        ValidatorInterface $validator,
        ExecutionContext $context,
    ): void {
        $condition = ['type' => 'family', 'operator' => 'IN', 'value' => ['shirts']];

        $validator->validate($condition, Argument::any())->shouldBeCalledTimes(2);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($condition, new FamilyShouldBeValid());
    }

    public function it_should_build_violation_for_non_existing_families(
        ValidatorInterface $validator,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
    ): void {
        $condition = ['type' => 'family', 'operator' => 'IN', 'value' => ['shirts', 'not_existing1', 'not_existing2']];

        $validator->validate($condition, Argument::any())->shouldBeCalledTimes(2);
        $context->buildViolation(Argument::any(), [
            '{{ familyCodes }}' => '"not_existing1", "not_existing2"',
        ])->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('value')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate($condition, new FamilyShouldBeValid());
    }
}
