<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SelectOptionShouldExist;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SelectOptionShouldExistValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SelectOptionShouldExistValidatorSpec extends ObjectBehavior
{
    public function let(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues,
        ExecutionContext $context
    ): void {
        $this->beConstructedWith($getExistingAttributeOptionsWithValues);
        $this->initialize($context);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SelectOptionShouldExistValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [
                ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN', 'value' => ['green', 'red']
            ], new NotBlank()]);
    }

    public function it_should_not_validate_something_else_than_an_array(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new SelectOptionShouldExist());
    }

    public function it_should_not_validate_if_attribute_code_is_missing(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(
            ['type' => 'simple_select', 'operator' => 'IN', 'value' => ['green', 'red']],
            new SelectOptionShouldExist()
        );
    }

    public function it_should_not_validate_if_value_is_missing(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(
            ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN'],
            new SelectOptionShouldExist()
        );
    }

    public function it_should_not_validate_if_value_is_not_an_array(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(
            ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN', 'value' => 'green'],
            new SelectOptionShouldExist()
        );
    }

    public function it_should_not_validate_if_value_is_empty(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(
            ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN', 'value' => []],
            new SelectOptionShouldExist()
        );
    }

    public function it_should_not_validate_if_value_is_not_an_array_of_strings(ExecutionContext $context): void
    {
        $context->buildViolation((string)Argument::any())->shouldNotBeCalled();

        $this->validate(
            ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN', 'value' => ['green', 0]],
            new SelectOptionShouldExist()
        );
    }

    public function it_should_add_violation_if_codes_do_not_exist(
        GetExistingAttributeOptionsWithValues $getExistingAttributeOptionsWithValues,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder,
    ) {
        $context->buildViolation(Argument::any(), [
            '{{ attributeCode }}' => 'color',
            '{{ optionCodes }}' => '"unknown1", "unknown2"',
        ])->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->atPath('[value]')->shouldBeCalled()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $getExistingAttributeOptionsWithValues->fromAttributeCodeAndOptionCodes([
            'color.green',
            'color.unknown1',
            'color.red',
            'color.unknown2',
        ])->shouldBeCalled()->willReturn([
            'color.green' => ['en_US' => 'Green'],
            'color.red' => ['en_US' => 'Red'],
        ]);

        $this->validate(
            ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN', 'value' => ['green', 'unknown1', 'red', 'unknown2']],
            new SelectOptionShouldExist()
        );
    }
}
