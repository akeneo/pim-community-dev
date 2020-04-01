<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\AttributeShouldBeNumeric;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ExistingCalculateActionField;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ExistingCalculateActionFieldValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ExistingCalculateActionFieldValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context, ValidatorInterface $validator)
    {
        $context->getValidator()->willReturn($validator);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ExistingCalculateActionFieldValidator::class);
    }

    function it_throw_an_exception_when_the_constraint_is_invalid()
    {
        $this->shouldThrow(UnexpectedTypeException::class)
             ->during('validate', [$this->productCalculateAction(), new IsNull()]);
    }

    function it_throws_an_exception_if_the_value_ius_not_a_product_calculate_action()
    {
        $this->shouldThrow(UnexpectedTypeException::class)
             ->during('validate', [new \stdClass(), new ExistingCalculateActionField()]);
    }

    function it_does_not_build_any_violation_if_values_are_valid(
        ExecutionContextInterface $context,
        ValidatorInterface $validator
    ) {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $validator->validate(Argument::type('string'), Argument::type(AttributeShouldBeNumeric::class))
            ->shouldBeCalledTimes(4)->willReturn(new ConstraintViolationList([]));
        $validator->validate(null, Argument::type(AttributeShouldBeNumeric::class))
            ->shouldBeCalledOnce()->willReturn(new ConstraintViolationList([]));

        $this->validate($this->productCalculateAction(), new ExistingCalculateActionField());
    }

    function it_builds_a_violation_if_destination_is_invalid(
        ExecutionContextInterface $context,
        ValidatorInterface $validator,
        ConstraintViolationBuilderInterface $violationBuilder,
        ConstraintViolationInterface $violation
    ) {
        $violation->getMessage()->willReturn('ErrorMessage');
        $violation->getParameters()->willReturn(['key' => 'value']);

        $context->buildViolation('ErrorMessage', ['key' => 'value'])->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('destination.field')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $validator->validate('volume', Argument::type(AttributeShouldBeNumeric::class))
            ->shouldBeCalled()->willReturn(new ConstraintViolationList([$violation->getWrappedObject()]));
        $validator->validate(Argument::not('volume'), Argument::type(AttributeShouldBeNumeric::class))
            ->willReturn(new ConstraintViolationList());

        $this->validate($this->productCalculateAction(), new ExistingCalculateActionField());
    }

    function it_builds_a_violation_if_source_field_is_invalid(
        ExecutionContextInterface $context,
        ValidatorInterface $validator,
        ConstraintViolationBuilderInterface $violationBuilder,
        ConstraintViolationInterface $violation
    ) {
        $violation->getMessage()->willReturn('ErrorMessage');
        $violation->getParameters()->willReturn(['key' => 'value']);

        $context->buildViolation('ErrorMessage', ['key' => 'value'])->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('source.field')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $validator->validate('height', Argument::type(AttributeShouldBeNumeric::class))
                  ->shouldBeCalled()->willReturn(new ConstraintViolationList([$violation->getWrappedObject()]));
        $validator->validate(Argument::not('height'), Argument::type(AttributeShouldBeNumeric::class))
                  ->willReturn(new ConstraintViolationList());

        $this->validate($this->productCalculateAction(), new ExistingCalculateActionField());
    }

    function it_builds_violations_if_operation_fields_are_invalid(
        ExecutionContextInterface $context,
        ValidatorInterface $validator,
        ConstraintViolationBuilderInterface $violationBuilder,
        ConstraintViolationInterface $violation
    ) {
        $violation->getMessage()->willReturn('ErrorMessage');
        $violation->getParameters()->willReturn(['key' => 'value']);

        $context->buildViolation('ErrorMessage', ['key' => 'value'])->shouldBeCalledTimes(2)->willReturn($violationBuilder);
        $violationBuilder->atPath('operation_list[0].field')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('operation_list[1].field')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalledTimes(2);

        $validator->validate('radius', Argument::type(AttributeShouldBeNumeric::class))
                  ->shouldBeCalledTimes(2)->willReturn(new ConstraintViolationList([$violation->getWrappedObject()]));
        $validator->validate(Argument::not('radius'), Argument::type(AttributeShouldBeNumeric::class))
                  ->willReturn(new ConstraintViolationList());

        $this->validate($this->productCalculateAction(), new ExistingCalculateActionField());
    }

    private function productCalculateAction(): ProductCalculateAction
    {
        return new ProductCalculateAction(
            [
                'destination' => [
                    'field' => 'volume',
                ],
                'source' => [
                    'field' => 'height',
                ],
                'operation_list' => [
                    [
                        'operator' => 'multiply',
                        'field' => 'radius',
                    ],
                    [
                        'operator' => 'multiply',
                        'field' => 'radius',
                    ],
                    [
                        'operator' => 'multiply',
                        'value' => 3.1415927
                    ],
                ]
            ]
        );
    }
}
