<?php

namespace spec\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Automation\RuleEngine\Bundle\Validator\Constraint\ExistingFilterField;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConditionInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ExistingFilterFieldValidatorSpec extends ObjectBehavior
{
    function let(FilterRegistryInterface $registry, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($registry);
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Pim\Automation\RuleEngine\Component\Validator\ExistingFilterFieldValidator');
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType('Symfony\Component\Validator\ConstraintValidator');
    }

    function it_adds_a_violation_if_no_filter_exists(
        $registry,
        $context,
        ProductConditionInterface $productCondition,
        ExistingFilterField $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $productCondition->getField()->willReturn('groups.code');
        $productCondition->getOperator()->willReturn('IN');
        $registry->getFilter('groups.code', 'IN')->willReturn(null);

        $context->buildViolation(
            Argument::any(),
            ['%field%' => 'groups.code', '%operator%' => 'IN']
        )->shouldBeCalled()
        ->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($productCondition, $constraint);
    }
}
