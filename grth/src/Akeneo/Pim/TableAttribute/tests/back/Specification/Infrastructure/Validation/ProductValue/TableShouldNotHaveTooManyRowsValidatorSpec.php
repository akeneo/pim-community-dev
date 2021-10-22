<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\TableShouldNotHaveTooManyRows;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\TableShouldNotHaveTooManyRowsValidator;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class TableShouldNotHaveTooManyRowsValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContext $executionContext)
    {
        $this->initialize($executionContext);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableShouldNotHaveTooManyRowsValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_should_throw_an_exception_with_wrong_constraint(
        TableValue $tableValue
    ) {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', [$tableValue, new Blank()]);
    }

    function it_should_only_validate_table_values(
        ExecutionContext $executionContext
    ) {
        $executionContext->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new TableShouldNotHaveTooManyRows());
    }

    function it_should_add_a_violation_when_there_are_too_many_rows(
        TableValue $tableValue,
        ExecutionContext $executionContext,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $executionContext
            ->buildViolation(Argument::any(), ['{{ limit }}' => 100])
            ->shouldBeCalled()
            ->willReturn($violationBuilder);
        $violationBuilder
            ->addViolation()
            ->shouldBeCalled();

        $normalized = [];
        for ($i = 0; $i < 101; $i++) {
            $normalized[] = [
                ColumnIdGenerator::ingredient() => sprintf('ingredient_%d', $i),
                ColumnIdGenerator::quantity() => 4,
            ];
        }

        $tableValue
            ->getData()
            ->shouldBeCalled()
            ->willReturn(Table::fromNormalized($normalized));

        $this->validate($tableValue, new TableShouldNotHaveTooManyRows());
    }

    function it_should_not_add_violation_when_there_are_not_too_many_rows(
        TableValue $tableValue,
        ExecutionContext $executionContext
    ) {
        $executionContext
            ->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $normalized = [];
        for ($i = 0; $i < 100; $i++) {
            $normalized[] = [
                ColumnIdGenerator::ingredient() => sprintf('ingredient_%d', $i),
                ColumnIdGenerator::quantity() => 4,
            ];
        }

        $tableValue
            ->getData()
            ->shouldBeCalled()
            ->willReturn(Table::fromNormalized($normalized));

        $this->validate($tableValue, new TableShouldNotHaveTooManyRows());
    }
}
