<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Condition;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ValueCondition;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ValueConditionValidator;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ValueConditionValidatorSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductQueryBuilderInterface $pqb,
        ExecutionContextInterface $context
    ) {
        $pqbFactory->create()->willReturn($pqb);
        $this->beConstructedWith($pqbFactory);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement(ConstraintValidatorInterface::class);
        $this->shouldHaveType(ValueConditionValidator::class);
    }

    function it_throws_an_exception_with_a_wrong_constraint()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'validate',
            [new Condition(['field' => 'name', 'operator' => 'EMPTY']), new IsNull()]
        );
    }

    function it_throws_an_exception_if_value_is_not_a_condition()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('validate', ['foo', new ValueCondition()]);
    }

    function it_does_nothing_if_field_is_empty(
        ProductQueryBuilderInterface $pqb,
        ExecutionContextInterface $context
    ) {
        $pqb->addFilter(Argument::cetera())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new Condition(['field' => null, 'operator' => 'EMPTY']), new ValueCondition());
    }

    function it_does_nothing_if_operator_is_empty(
        ProductQueryBuilderInterface $pqb,
        ExecutionContextInterface $context
    ) {
        $pqb->addFilter(Argument::cetera())->shouldNotBeCalled();
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(new Condition(['field' => 'name', 'operator' => '']), new ValueCondition());
    }

    function it_does_not_add_a_violation_if_the_filter_is_valid(
        ProductQueryBuilderInterface $pqb,
        ExecutionContextInterface $context
    ) {
        $pqb->addFilter('name', 'CONTAINS', 'foo', ['locale' => null, 'scope' => null])
            ->shouldBeCalled()->willReturn($pqb);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate(
            new Condition(['field' => 'name', 'operator' => 'CONTAINS', 'value' => 'foo']),
            new ValueCondition()
        );
    }

    function it_adds_a_violation_if_the_filter_is_not_valid(
        ProductQueryBuilderInterface $pqb,
        ExecutionContextInterface $context,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $pqb->addFilter('name', 'IN', ['camcorders'], ['locale' => 'en_US', 'scope' => 'ecommerce'])
            ->shouldBeCalled()->willThrow(
                new UnsupportedFilterException(
                    'Filter on property "name" is not supported or does not support operator "IN"'
                )
            );
        $constraint = new ValueCondition();
        $context->buildViolation(
            $constraint->message,
            [
                '%message%' => 'Filter on property "name" is not supported or does not support operator "IN"',
            ]
        )->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();

        $this->validate(
            new Condition(
                [
                    'field' => 'name',
                    'operator' => 'IN',
                    'value' => ['camcorders'],
                    'scope' => 'ecommerce',
                    'locale' => 'en_US',
                ]
            ),
            $constraint
        );
    }
}
