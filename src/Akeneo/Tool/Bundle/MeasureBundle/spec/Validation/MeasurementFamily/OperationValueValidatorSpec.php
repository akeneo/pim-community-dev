<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily\OperationValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class OperationValueValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    public function it_should_validate_operation_value_int_in_string(
        OperationValue $constraint,
        ExecutionContextInterface $context
    ) {
        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('1', $constraint);
    }

    public function it_should_validate_operation_value_float_in_string(
        OperationValue $constraint,
        ExecutionContextInterface $context
    ) {
        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('0.00000006', $constraint);
    }

    public function it_add_violation_when_operation_value_is_null(
        OperationValue $constraint,
        ExecutionContextInterface $context
    ) {
        $context
            ->addViolation('This value should not be blank.', ['{{ value }}' => 'null'])
            ->shouldBeCalled();

        $this->validate(null, $constraint);
    }

    public function it_add_violation_when_operation_value_is_empty(
        OperationValue $constraint,
        ExecutionContextInterface $context
    ) {
        $context
            ->addViolation('This value should not be blank.', ['{{ value }}' => '""'])
            ->shouldBeCalled();

        $this->validate('', $constraint);
    }

    public function it_add_violation_when_operation_value_is_an_array(
        OperationValue $constraint,
        ExecutionContextInterface $context
    ) {
        $context
            ->addViolation('pim_measurements.validation.measurement_family.convert.value_should_be_a_number_in_a_string', [])
            ->shouldBeCalled();

        $this->validate(['value' => '10'], $constraint);
    }

    public function it_add_violation_when_operation_value_is_a_number(
        OperationValue $constraint,
        ExecutionContextInterface $context
    ) {
        $context
            ->addViolation('pim_measurements.validation.measurement_family.convert.value_should_be_a_number_in_a_string', [])
            ->shouldBeCalled();

        $this->validate(16.88888, $constraint);
    }

    public function it_add_violation_when_operation_value_is_a_scientific_notation(
        OperationValue $constraint,
        ExecutionContextInterface $context
    ) {
        $context
            ->addViolation('pim_measurements.validation.measurement_family.convert.value_should_be_a_number_in_a_string', [])
            ->shouldBeCalled();

        $this->validate('10E-7', $constraint);
    }
}
