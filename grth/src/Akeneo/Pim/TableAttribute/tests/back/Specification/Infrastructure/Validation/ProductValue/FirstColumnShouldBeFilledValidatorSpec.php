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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\FirstColumnShouldBeFilled;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\FirstColumnShouldBeFilledValidator;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class FirstColumnShouldBeFilledValidatorSpec extends ObjectBehavior
{
    function let(TableConfigurationRepository $tableConfigurationRepository, ExecutionContext $context)
    {
        $this->beConstructedWith($tableConfigurationRepository);
        $this->initialize($context);

        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            ])
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FirstColumnShouldBeFilledValidator::class);
        $this->shouldImplement(ConstraintValidatorInterface::class);
    }

    function it_throws_an_exception_with_the_wrong_constraint_type()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'validate',
                [
                    TableValue::value('nutrition', Table::fromNormalized([[ColumnIdGenerator::ingredient() => 'sugar']])),
                    new NotBlank()
                ]
            );
    }

    function it_does_nothing_when_the_value_is_not_table_value(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate('foo', new FirstColumnShouldBeFilled());
    }

    function it_adds_a_violation_when_first_column_is_not_filled(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $tableValue = TableValue::value('nutrition', Table::fromNormalized([
            [ColumnIdGenerator::quantity() => 'sugar'],
            [ColumnIdGenerator::ingredient() => 'salt', ColumnIdGenerator::quantity() => 'sugar'],
            [ColumnIdGenerator::quantity() => 'sugar'],
        ]));
        $constraint = new FirstColumnShouldBeFilled();
        $context->buildViolation($constraint->message, ['{{ columnCode }}' => 'ingredient'])
            ->shouldBeCalledTimes(2)->willReturn($violationBuilder);
        $violationBuilder->atPath('[0]')->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->atPath('[2]')->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledTimes(2);

        $this->validate($tableValue, $constraint);
    }

    function it_does_not_add_violation_when_first_column_is_filled(ExecutionContext $context)
    {
        $tableValue = TableValue::value('nutrition', Table::fromNormalized([
            [ColumnIdGenerator::ingredient() => 'sugar'],
            [ColumnIdGenerator::ingredient() => 'salt'],
        ]));
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($tableValue, new FirstColumnShouldBeFilled());
    }
}
