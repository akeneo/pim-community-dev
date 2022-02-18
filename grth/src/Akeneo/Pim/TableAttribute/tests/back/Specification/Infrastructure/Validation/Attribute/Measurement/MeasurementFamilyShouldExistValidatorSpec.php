<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\MeasurementFamilyExists;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement\MeasurementFamilyShouldExist;
use Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement\MeasurementFamilyShouldExistValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class MeasurementFamilyShouldExistValidatorSpec extends ObjectBehavior
{
    function let(MeasurementFamilyExists $measurementFamilyExists, ExecutionContextInterface $context)
    {
        $measurementFamilyExists->forCode('duration')->willReturn(true);
        $measurementFamilyExists->forCode('unknown')->willReturn(false);

        $this->beConstructedWith($measurementFamilyExists);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(MeasurementFamilyShouldExistValidator::class);
    }

    function it_throws_an_exception_with_the_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            ['duration', new NotBlank()]
        );
    }

    function it_does_nothing_when_value_is_not_a_string(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate(true, new MeasurementFamilyShouldExist());
    }

    function it_does_not_add_violation_when_measurement_family_exists(ExecutionContext $context)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate('duration', new MeasurementFamilyShouldExist());
    }

    function it_adds_violation_when_measurement_family_does_not_exist(
        ExecutionContext $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $constraint = new MeasurementFamilyShouldExist();
        $context->buildViolation($constraint->message, ['{{ measurement_family_code }}' => 'unknown'])
            ->shouldBeCalledOnce()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledOnce();
        $this->validate('unknown', $constraint);
    }
}
