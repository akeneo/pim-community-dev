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

use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\IsColumnCodeUnique;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\IsColumnCodeUniqueValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsColumnCodeUniqueValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContext $context)
    {
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(IsColumnCodeUniqueValidator::class);
    }

    function it_should_throw_an_exception_with_the_wrong_constraint_type()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [[['code' => 'a'], ['code' => 'b'], ['code' => 'c']], new NotBlank()]
        );
    }

    function it_does_nothing_when_value_is_not_an_array(ExecutionContext $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('a string', new IsColumnCodeUnique());
    }

    function it_does_nothing_when_value_does_not_contain_any_code(ExecutionContext $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate([['foo' => 'bar'], 'baz'], new IsColumnCodeUnique());
    }

    function it_does_nothing_when_codes_are_unique(ExecutionContext $context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate([['code' => 'a'], ['code' => 'b'], ['code' => 'c']], new IsColumnCodeUnique());
    }

    function it_throws_an_exception_when_codes_are_not_unique(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $context->buildViolation(
            'pim_table_configuration.validation.table_configuration.duplicated_column_code',
            ['%duplicateCodes%' => 'a', '%count%' => 1]
        )->shouldBeCalledOnce()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate([['code' => 'a'], ['code' => 'b'], ['code' => 'a'], ['code' => 'c']], new IsColumnCodeUnique());
    }

    function it_throws_an_exception_when_codes_are_not_unique_with_case_insensitive(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $context->buildViolation(
            'pim_table_configuration.validation.table_configuration.duplicated_column_code',
            ['%duplicateCodes%' => 'AAA, AAa', '%count%' => 2]
        )->shouldBeCalledOnce()->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate([['code' => 'aAa'], ['code' => 'b'], ['code' => 'AAA'], ['code' => 'c'], ['code' => 'AAa']], new IsColumnCodeUnique());
    }
}
