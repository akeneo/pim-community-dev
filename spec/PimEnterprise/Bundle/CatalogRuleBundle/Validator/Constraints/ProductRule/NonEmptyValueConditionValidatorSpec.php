<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule ;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Query\Filter\Operators;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\NonEmptyValueCondition;
use Prophecy\Argument;
use Symfony\Component\Validator\ExecutionContextInterface;

class NonEmptyValueConditionValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\ProductRule\NonEmptyValueConditionValidator');
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType('Symfony\Component\Validator\ConstraintValidator');
    }

    function it_adds_a_violation_if_the_operator_is_not_empty_and_the_value_is_empty(
        $context,
        ProductConditionInterface $condition,
        NonEmptyValueCondition $constraint
    ) {
        $condition->getOperator()->willReturn('foo');
        $condition->getValue()->willReturn(null);

        $context->addViolation(Argument::any(), [])->shouldBeCalled();

        $this->validate($condition, $constraint);
    }

    function it_does_not_add_a_violation_if_the_operator_is_empty_and_the_value_is_empty(
        ProductConditionInterface $condition,
        NonEmptyValueCondition $constraint
    ) {
        $condition->getOperator()->willReturn(Operators::IS_EMPTY);
        $condition->getValue()->willReturn(null);

        $this->validate($condition, $constraint);
    }
}
