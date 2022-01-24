<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement\MeasurementFamilyCannotBeUpdated;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement\MeasurementFamilyCannotBeUpdatedValidator;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class MeasurementFamilyCannotBeUpdatedValidatorSpec extends ObjectBehavior
{
    function let(
        TableConfigurationRepository $tableConfigurationRepository,
        ExecutionContextInterface $context
    ) {
        $tableAttribute = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity', 'validations' => ['min' => 5, 'max' => 20]]),
            MeasurementColumn::fromNormalized([
                'id' => ColumnIdGenerator::duration(),
                'code' => 'manufacturing_time',
                'measurement_family_code' => 'duration',
                'measurement_default_unit_code' => 'second',
            ]),
        ]);
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn($tableAttribute);

        $attribute = new Attribute();
        $attribute->setCode('nutrition');
        $context->getRoot()->willReturn($attribute);

        $this->beConstructedWith($tableConfigurationRepository);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(MeasurementFamilyCannotBeUpdatedValidator::class);
    }

    function it_throws_an_exception_with_the_wrong_constraint()
    {
        $column = [
            'code' => 'manufacturing_time',
            'data_type' => MeasurementColumn::DATATYPE,
            'measurement_family_code' => 'duration',
            'measurement_default_unit_code' => 'second',
        ];
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [$column, new NotBlank()]
        );
    }

    function it_does_nothing_when_value_is_not_an_array(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(true, new MeasurementFamilyCannotBeUpdated());
    }

    function it_does_nothing_when_value_does_not_contain_string_code(ExecutionContext $context)
    {
        $column = [
            'code' => [],
            'data_type' => MeasurementColumn::DATATYPE,
            'measurement_family_code' => 'other',
            'measurement_default_unit_code' => 'second',
        ];
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($column, new MeasurementFamilyCannotBeUpdated());
    }

    function it_does_nothing_when_value_does_not_contain_string_measurement_family_code(ExecutionContext $context)
    {
        $column = [
            'code' => 'manufacturing_time',
            'data_type' => [],
            'measurement_family_code' => [],
            'measurement_default_unit_code' => 'second',
        ];
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($column, new MeasurementFamilyCannotBeUpdated());
    }

    function it_does_nothing_when_data_type_is_a_string(ExecutionContext $context)
    {
        $column = [
            'code' => 'manufacturing_time',
            'data_type' => SelectColumn::DATATYPE,
            'measurement_family_code' => [],
            'measurement_default_unit_code' => 'second',
        ];
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($column, new MeasurementFamilyCannotBeUpdated());
    }

    function it_does_nothing_when_data_type_is_not_measurement(ExecutionContext $context)
    {
        $column = [
            'code' => 'manufacturing_time',
            'data_type' => SelectColumn::DATATYPE,
            'measurement_family_code' => 'other',
            'measurement_default_unit_code' => 'second',
        ];
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($column, new MeasurementFamilyCannotBeUpdated());
    }

    function it_does_nothing_when_column_code_is_not_valid(ExecutionContext $context)
    {
        $column = [
            'code' => '',
            'data_type' => MeasurementColumn::DATATYPE,
            'measurement_family_code' => 'other',
            'measurement_default_unit_code' => 'second',
        ];
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($column, new MeasurementFamilyCannotBeUpdated());
    }

    function it_does_nothing_when_measurement_family_code_is_not_valid(ExecutionContext $context)
    {
        $column = [
            'code' => 'manufacturing_time',
            'data_type' => MeasurementColumn::DATATYPE,
            'measurement_family_code' => '',
            'measurement_default_unit_code' => 'second',
        ];
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($column, new MeasurementFamilyCannotBeUpdated());
    }

    function it_validates_the_value_when_the_attribute_is_new(
        TableConfigurationRepository $tableConfigurationRepository,
        ExecutionContext $context
    ) {
        $column = [
            'code' => 'manufacturing_time',
            'data_type' => MeasurementColumn::DATATYPE,
            'measurement_family_code' => 'other',
            'measurement_default_unit_code' => 'second',
        ];
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willThrow(new TableConfigurationNotFoundException());

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($column, new MeasurementFamilyCannotBeUpdated());
    }

    function it_validates_the_value_when_the_column_is_new(
        ExecutionContext $context
    ) {
        $column = [
            'code' => 'manufacturing_time_new',
            'data_type' => MeasurementColumn::DATATYPE,
            'measurement_family_code' => 'other',
            'measurement_default_unit_code' => 'second',
        ];

        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($column, new MeasurementFamilyCannotBeUpdated());
    }

    function it_validates_the_column_if_the_measurement_family_stays_the_same(ExecutionContext $context)
    {
        $column = [
            'code' => 'manufacturing_time',
            'data_type' => MeasurementColumn::DATATYPE,
            'measurement_family_code' => 'duration',
            'measurement_default_unit_code' => 'second',
        ];
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($column, new MeasurementFamilyCannotBeUpdated());
    }

    function it_adds_a_violation_when_the_measurement_family_is_modified(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $column = [
            'code' => 'manufacturing_time',
            'data_type' => MeasurementColumn::DATATYPE,
            'measurement_family_code' => 'other',
            'measurement_default_unit_code' => 'second',
        ];
        $constraint = new MeasurementFamilyCannotBeUpdated();
        $context->buildViolation(
            $constraint->message,
            [
                '{{ column_code }}' => 'manufacturing_time',
                '{{ given_measurement_family_code }}' => 'other',
                '{{ former_measurement_family_code }}' => 'duration',
            ]
        )->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->atPath('measurement_family_code')->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($column, $constraint);
    }
}
