<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetNonExistingSelectOptionCodes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\SelectOptionsShouldExist;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue\SelectOptionsShouldExistValidator;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class SelectOptionsShouldExistValidatorSpec extends ObjectBehavior
{
    function let(
        TableConfigurationRepository $tableConfigurationRepository,
        GetNonExistingSelectOptionCodes $getNonExistingSelectOptionCodes,
        ExecutionContext $context
    ) {
        $this->beConstructedWith($tableConfigurationRepository, $getNonExistingSelectOptionCodes);
        $this->initialize($context);

        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'options' => [
                ['code' => 'salt'],
                ['code' => 'sugar'],
            ], 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::supplier(), 'code' => 'supplier', 'options' => [
                ['code' => 'Akeneo'],
            ]]),
        ]);
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn($tableConfiguration);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(SelectOptionsShouldExistValidator::class);
    }

    function it_throws_an_exception_when_provided_with_the_wrong_constraint(ValueInterface $value)
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [$value, new NotBlank()]
        );
    }

    function it_does_nothing_when_value_is_not_a_table_value(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new \stdClass(), new SelectOptionsShouldExist());
    }

    function it_builds_a_violation_when_an_option_does_not_exist(
        ExecutionContext $context,
        GetNonExistingSelectOptionCodes $getNonExistingSelectOptionCodes,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new SelectOptionsShouldExist();
        $tableValue = TableValue::value('nutrition', Table::fromNormalized([
            [ColumnIdGenerator::ingredient() => 'unknown_ingredient', ColumnIdGenerator::quantity() => 'foo'],
            [ColumnIdGenerator::ingredient() => 'salt', ColumnIdGenerator::isAllergenic() => true, ColumnIdGenerator::supplier() => 'unknown_supplier'],
        ]));

        $getNonExistingSelectOptionCodes
            ->forOptionCodes('nutrition', ColumnCode::fromString('ingredient'), [SelectOptionCode::fromString('unknown_ingredient'), SelectOptionCode::fromString('salt')])
            ->willReturn([SelectOptionCode::fromString('unknown_ingredient')]);
        $getNonExistingSelectOptionCodes
            ->forOptionCodes('nutrition', ColumnCode::fromString('supplier'), [SelectOptionCode::fromString('unknown_supplier')])
            ->willReturn([SelectOptionCode::fromString('unknown_supplier')]);

        $context->buildViolation($constraint->message, ['{{ optionCode }}' => 'unknown_ingredient'])
            ->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->atPath('[0].ingredient')->shouldBeCalledOnce()->willReturn($violationBuilder);
        $context->buildViolation($constraint->message, ['{{ optionCode }}' => 'unknown_supplier'])
            ->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->atPath('[1].supplier')->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledTimes(2);

        $this->validate($tableValue, $constraint);
    }

    function it_does_not_build_any_violation_when_all_options_exist(
        GetNonExistingSelectOptionCodes $getNonExistingSelectOptionCodes,
        ExecutionContext $context
    ) {
        $constraint = new SelectOptionsShouldExist();
        $tableValue = TableValue::value('nutrition', Table::fromNormalized([
            [ColumnIdGenerator::ingredient() => 'sugar', ColumnIdGenerator::quantity() => 'foo'],
            [ColumnIdGenerator::ingredient() => 'salt', ColumnIdGenerator::isAllergenic() => true, ColumnIdGenerator::supplier() => 'Akeneo'],
        ]));

        $getNonExistingSelectOptionCodes
            ->forOptionCodes('nutrition', ColumnCode::fromString('ingredient'), [SelectOptionCode::fromString('sugar'), SelectOptionCode::fromString('salt')])
            ->willReturn([]);
        $getNonExistingSelectOptionCodes
            ->forOptionCodes('nutrition', ColumnCode::fromString('supplier'), [SelectOptionCode::fromString('Akeneo')])
            ->willReturn([]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($tableValue, $constraint);
    }

    function it_does_nothing_when_cell_values_are_not_a_string(
        GetNonExistingSelectOptionCodes $getNonExistingSelectOptionCodes,
        ExecutionContext $context
    ) {
        $tableValue = TableValue::value('nutrition', Table::fromNormalized([
            [ColumnIdGenerator::ingredient() => true, ColumnIdGenerator::quantity() => 'foo'],
            [ColumnIdGenerator::ingredient() => 'salt', ColumnIdGenerator::isAllergenic() => true, ColumnIdGenerator::supplier() => 4],
        ]));

        $getNonExistingSelectOptionCodes
            ->forOptionCodes('nutrition', ColumnCode::fromString('ingredient'), [SelectOptionCode::fromString('salt')])
            ->willReturn([]);

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($tableValue, new SelectOptionsShouldExist());
    }
}
