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

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\CellDataTypesShouldMatch;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\CellDataTypesShouldMatchValidator;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Pim\TableAttribute\tests\back\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class CellDataTypesShouldMatchValidatorSpec extends ObjectBehavior
{
    function let(TableConfigurationRepository $tableConfigurationRepository, ExecutionContext $context)
    {
        $this->beConstructedWith($tableConfigurationRepository);
        $this->initialize($context);

        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient']),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
                BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'isAllergen']),
            ])
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CellDataTypesShouldMatchValidator::class);
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

    function it_does_nothing_when_the_value_is_not_table_value(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('pouet', new CellDataTypesShouldMatch());
    }

    function it_adds_a_violation_on_invalid_types(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $tableValue = TableValue::value('nutrition', Table::fromNormalized([
            [ColumnIdGenerator::ingredient() => 12, ColumnIdGenerator::quantity() => 1],
            [ColumnIdGenerator::ingredient() => 'pepper', ColumnIdGenerator::quantity() => 'foo'],
            [ColumnIdGenerator::ingredient() => 'salt', ColumnIdGenerator::isAllergenic() => 'yes'],
        ]));

        $context->buildViolation(Argument::type('string'), ['{{ expected }}' => 'string', '{{ given }}' => 'integer', '{{ columnCode }}' => 'ingredient'])
            ->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[0].ingredient')->shouldBeCalledOnce()->willReturn($violationBuilder);

        $context->buildViolation(Argument::type('string'), ['{{ expected }}' => 'numeric', '{{ given }}' => 'string', '{{ columnCode }}' => 'quantity'])
            ->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[1].quantity')->shouldBeCalledOnce()->willReturn($violationBuilder);

        $context->buildViolation(Argument::type('string'), ['{{ expected }}' => 'boolean', '{{ given }}' => 'string', '{{ columnCode }}' => 'isAllergen'])
            ->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('[2].isAllergen')->shouldBeCalledOnce()->willReturn($violationBuilder);

        $violationBuilder->addViolation()->shouldBeCalledTimes(3);

        $this->validate($tableValue, new CellDataTypesShouldMatch());
    }

    function it_does_not_add_violation_when_every_type_is_valid(ExecutionContext $context)
    {
        $tableValue = TableValue::value('nutrition', Table::fromNormalized([[
            ColumnIdGenerator::ingredient() => 'red hot chili peppers',
            ColumnIdGenerator::quantity() => 4,
            ColumnIdGenerator::isAllergenic() => true,
        ]]));

        $context->buildViolation(Argument::cetera())
            ->shouldNotBeCalled();

        $this->validate($tableValue, new CellDataTypesShouldMatch());
    }

    function it_does_not_validate_when_column_is_unknown(ExecutionContext $context)
    {
        $tableValue = TableValue::value('nutrition', Table::fromNormalized([[
            'unknown' => 'foo',
        ]]));

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($tableValue, new CellDataTypesShouldMatch());
    }
}
