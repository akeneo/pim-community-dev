<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\MeasurementUnitExists;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement\MeasurementUnitShouldExist;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement\MeasurementUnitShouldExistValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class MeasurementUnitShouldExistValidatorSpec extends ObjectBehavior
{
    function let(MeasurementUnitExists $measurementUnitExists, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($measurementUnitExists);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(MeasurementUnitShouldExistValidator::class);
    }

    function it_throws_an_exception_with_the_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            ['duration', new NotBlank()]
        );
    }

    function it_does_nothing_when_value_is_not_an_array(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate('second', new MeasurementUnitShouldExist());
    }

    function it_does_nothing_when_measurement_family_code_is_not_provided(ExecutionContext $context)
    {
        $value = ['measurement_default_unit_code' => 'second'];
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($value, new MeasurementUnitShouldExist());
    }

    function it_does_nothing_when_measurement_unit_code_is_not_provided(ExecutionContext $context)
    {
        $value = ['measurement_family_code' => 'duration'];
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($value, new MeasurementUnitShouldExist());
    }

    function it_validates_the_value_when_unit_exists(
        MeasurementUnitExists $measurementUnitExists,
        ExecutionContext $context
    ) {
        $value = ['measurement_family_code' => 'duration', 'measurement_default_unit_code' => 'second'];
        $measurementUnitExists->inFamily('duration', 'second')->willReturn(true);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($value, new MeasurementUnitShouldExist());
    }

    function it_adds_a_violation_when_the_unit_does_not_exist(
        MeasurementUnitExists $measurementUnitExists,
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $value = ['measurement_family_code' => 'duration', 'measurement_default_unit_code' => 'second'];
        $constraint = new MeasurementUnitShouldExist();

        $measurementUnitExists->inFamily('duration', 'second')->willReturn(false);
        $context->buildViolation(
            $constraint->message,
            ['{{ measurement_family_code }}' => 'duration', '{{ measurement_unit_code }}' => 'second']
        )->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();

        $this->validate($value, new MeasurementUnitShouldExist());
    }
}
