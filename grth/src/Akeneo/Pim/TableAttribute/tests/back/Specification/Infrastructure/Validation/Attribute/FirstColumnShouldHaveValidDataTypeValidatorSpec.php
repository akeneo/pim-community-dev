<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute;

use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\FirstColumnShouldHaveValidDataType;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\FirstColumnShouldHaveValidDataTypeValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

final class FirstColumnShouldHaveValidDataTypeValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContextInterface $context)
    {
        $this->beConstructedWith($this->getAllowedFirstColumnDatatypes());
        $this->initialize($context);
    }

    private function getAllowedFirstColumnDatatypes(): array
    {
        return [ "select" ];
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(FirstColumnShouldHaveValidDataTypeValidator::class);
    }

    function it_throws_an_exception_when_provided_with_an_invalid_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [[], new NotBlank()]);
    }

    function it_does_nothing_when_value_is_not_an_array(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate('table', new FirstColumnShouldHaveValidDataType());
    }

    function it_does_nothing_when_data_type_is_not_provided_for_first_column(ExecutionContext $context)
    {
        $config = [
            ['code' => 'ingredients'],
            ['data_type' => 'text'],
        ];

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate('table', new FirstColumnShouldHaveValidDataType());
    }

    function it_does_nothing_when_data_type_is_not_a_string(ExecutionContext $context)
    {
        $config = [
            ['data_type' => 1],
            ['data_type' => 'text'],
        ];

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate('table', new FirstColumnShouldHaveValidDataType());
    }

    function it_does_not_add_any_violation_when_first_column_data_type_is_select(ExecutionContext $context)
    {
        $config = [
            ['data_type' => 'select'],
            ['data_type' => 'text'],
        ];

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($config, new FirstColumnShouldHaveValidDataType());
    }

    function it_adds_a_violation_when_first_column_data_type_is_not_select(
        ExecutionContext $context,
        ConstraintViolationBuilder $violationBuilder
    ) {
        $constraint = new FirstColumnShouldHaveValidDataType();
        $config = [
            ['data_type' => 'number'],
            ['data_type' => 'text'],
        ];

        $context->buildViolation(
            $constraint->message,
            [
                '{{ data_type }}' => 'number',
                '{{ allowed_data_types }}' => 'select',
                '{{ allowed_data_types_except_last }}' => '',
                '{{ last_allowed_data_types }}' => 'select',
                '%count%' => count($this->getAllowedFirstColumnDatatypes()),
            ]
            )->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder->atPath('[0].data_type')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($config, $constraint);
    }
}
