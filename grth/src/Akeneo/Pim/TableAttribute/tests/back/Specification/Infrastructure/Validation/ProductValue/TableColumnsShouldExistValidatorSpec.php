<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\TableColumnsShouldExist;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\TableColumnsShouldExistValidator;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class TableColumnsShouldExistValidatorSpec extends ObjectBehavior
{
    function let(TableConfigurationRepository $tableConfigurationRepository, ExecutionContext $context)
    {
        $this->beConstructedWith($tableConfigurationRepository);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableColumnsShouldExistValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_the_wrong_constraint_type()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
             ->during(
                 'validate',
                 [TableValue::value('nutrition', Table::fromNormalized([['ingredient' => 'sugar']])), new NotBlank()]
             );
    }

    function it_adds_a_violation_when_a_column_does_not_exist(
        TableConfigurationRepository $tableConfigurationRepository,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            ])
        );


        $context->buildViolation(
            Argument::type('string'),
            [
                '{{ non_existing_columns }}' => 'non_existing',
                '%count%' => 1,
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            TableValue::value('nutrition', Table::fromNormalized([[ColumnIdGenerator::ingredient() => 'sugar', 'non_existing' => 'kiwi']])),
            new TableColumnsShouldExist()
        );
    }

    function it_adds_a_violation_when_several_columns_do_not_exist(
        TableConfigurationRepository $tableConfigurationRepository,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->shouldBeCalled()->willReturn(
            TableConfiguration::fromColumnDefinitions(
                [
                    SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
                    NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
                ]
            )
        );

        $context->buildViolation(
            Argument::type('string'),
            [
                '{{ non_existing_columns }}' => 'non_existing, other',
                '%count%' => 2,
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            TableValue::value(
                'nutrition',
                Table::fromNormalized([[ColumnIdGenerator::ingredient() => 'sugar', 'non_existing' => 'kiwi', 'other' => 'foobar']])
            ),
            new TableColumnsShouldExist()
        );
    }

    function it_does_not_validate_a_non_table_value(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('toto', new TableColumnsShouldExist());
    }
}
