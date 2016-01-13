<?php

namespace spec\PimEnterprise\Component\CatalogRule\Validator\Constraints\ProductRule;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Query\Filter\FilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FilterRegistryInterface;
use PimEnterprise\Component\CatalogRule\Model\ProductConditionInterface;
use PimEnterprise\Component\CatalogRule\Validator\Constraints\ProductRule\SupportedOperatorCondition;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class SupportedOperatorConditionValidatorSpec extends ObjectBehavior
{
    function let(FilterRegistryInterface $registry, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($registry);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Validator\Constraints\Productfule\SupportedOperatorConditionValidator');
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType('Symfony\Component\Validator\ConstraintValidator');
    }

    function it_adds_a_violation_if_the_operator_is_not_supported(
        $registry,
        $context,
        ProductConditionInterface $condition,
        FilterInterface $filter,
        SupportedOperatorCondition $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $condition->getField()->willReturn('description');
        $condition->getOperator()->willReturn('NOT SUPPORTED');
        $filter->supportsOperator('NOT SUPPORTED')->willReturn(false);
        $registry->getFilter('description')->willReturn($filter);

        $context->buildViolation(Argument::any(), ['%field%' => 'description', '%operator%' => 'NOT SUPPORTED'])
            ->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($condition, $constraint);
    }
}
