<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement;

use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement\MeasurementInfoShouldOnlyBeSetOnMeasurementColumns;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement\MeasurementInfoShouldOnlyBeSetOnMeasurementColumnsValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class MeasurementInfoShouldOnlyBeSetOnMeasurementColumnsValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(MeasurementInfoShouldOnlyBeSetOnMeasurementColumnsValidator::class);
    }

    function it_throws_an_exception_with_the_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [['data_type' => 'text'], new NotBlank()]
        );
    }

    function it_does_nothing_when_value_is_not_an_array(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(true, new MeasurementInfoShouldOnlyBeSetOnMeasurementColumns());
    }

    function it_does_nothing_when_value_does_not_have_data_type(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(['code' => 'measurement'], new MeasurementInfoShouldOnlyBeSetOnMeasurementColumns());
    }

    function it_does_nothing_when_value_does_not_have_a_valid_data_type(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(['data_type' => true], new MeasurementInfoShouldOnlyBeSetOnMeasurementColumns());
    }

    function it_is_valid_when_the_column_is_not_a_measurement_and_does_not_contain_any_measurement_info(
        ExecutionContext $context
    ) {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(['data_type' => 'text'], new MeasurementInfoShouldOnlyBeSetOnMeasurementColumns());
    }

    function it_is_valid_when_the_column_is_a_measurement_and_contains_all_measurement_info(
        ExecutionContext $context
    ) {
        $column = [
            'data_type' => 'measurement',
            'measurement_family_code' => 'duration',
            'measurement_default_unit_code' => 'second',
        ];
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($column, new MeasurementInfoShouldOnlyBeSetOnMeasurementColumns());
    }

    function it_adds_violations_when_the_column_is_a_measurement_and_does_not_contain_all_measurement_info(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $column = ['data_type' => 'measurement'];

        $context->buildViolation('pim_table_configuration.validation.table_configuration.measurement_family_code_must_be_filled')
            ->shouldBeCalledOnce()->willReturn($violationBuilder);
        $context->buildViolation('pim_table_configuration.validation.table_configuration.measurement_default_unit_code_must_be_filled')
            ->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledTimes(2);

        $this->validate($column, new MeasurementInfoShouldOnlyBeSetOnMeasurementColumns());
    }

    function it_adds_violations_when_the_column_is_not_a_measurement_and_contains_measurement_info(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $column = [
            'data_type' => 'text',
            'measurement_family_code' => 'duration',
            'measurement_default_unit_code' => 'second',
        ];
        $context->buildViolation(
            'pim_table_configuration.validation.table_configuration.measurement_family_code_cannot_be_set',
            ['{{ data_type }}' => 'text']
        )->shouldBeCalledOnce()->willReturn($violationBuilder);
        $context->buildViolation(
            'pim_table_configuration.validation.table_configuration.measurement_default_unit_code_cannot_be_set',
            ['{{ data_type }}' => 'text']
        )->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledTimes(2);

        $this->validate($column, new MeasurementInfoShouldOnlyBeSetOnMeasurementColumns());
    }
}
