<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\CalculateActionFieldsValidator;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\AttributeShouldBeNumeric;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\CalculateActionFields;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CalculateActionFieldsValidatorSpec extends ObjectBehavior
{
    function let(
        ExecutionContextInterface $context,
        ValidatorInterface $validator,
        ContextualValidatorInterface $contextualValidator
    ) {
        $validator->inContext($context)->willReturn($contextualValidator);
        $context->getValidator()->willReturn($validator);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(CalculateActionFieldsValidator::class);
    }

    function it_throws_an_exception_when_the_constraint_is_invalid()
    {
        $this->shouldThrow(UnexpectedTypeException::class)
             ->during('validate', [$this->productCalculateAction(), new IsNull()]);
    }

    function it_throws_an_exception_if_the_value_is_not_a_product_calculate_action()
    {
        $this->shouldThrow(UnexpectedTypeException::class)
             ->during('validate', [new \stdClass(), new CalculateActionFields()]);
    }

    function it_validates_that_source_destination_and_operations_attributes_are_numeric(
        ContextualValidatorInterface $contextualValidator
    ) {
        $contextualValidator->atPath('destination.field')->shouldBeCalled()->willReturn($contextualValidator);
        $contextualValidator->validate('volume', new AttributeShouldBeNumeric())->shouldBeCalled();

        $contextualValidator->atPath('source.field')->shouldBeCalled()->willReturn($contextualValidator);
        $contextualValidator->validate('height', new AttributeShouldBeNumeric())->shouldBeCalled();

        $contextualValidator->atPath('operation_list[0].field')->shouldBeCalled()->willReturn($contextualValidator);
        $contextualValidator->validate('radius', new AttributeShouldBeNumeric())->shouldBeCalled();

        $contextualValidator->atPath('operation_list[1].field')->shouldBeCalled()->willReturn($contextualValidator);
        $contextualValidator->validate('radius', new AttributeShouldBeNumeric())->shouldBeCalled();

        $contextualValidator->atPath('operation_list[2].field')->shouldBeCalled()->willReturn($contextualValidator);
        $contextualValidator->validate(null, new AttributeShouldBeNumeric())->shouldBeCalled();

        $this->validate($this->productCalculateAction(), new CalculateActionFields());
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
