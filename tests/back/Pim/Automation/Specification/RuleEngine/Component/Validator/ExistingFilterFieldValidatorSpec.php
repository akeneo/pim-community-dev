<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\DTO\Condition;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\ExistingFilterField;
use Akeneo\Pim\Automation\RuleEngine\Component\Validator\ExistingFilterFieldValidator;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintValidator;
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
        $this->shouldHaveType(ExistingFilterFieldValidator::class);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType(ConstraintValidator::class);
    }

    function it_adds_a_violation_if_no_filter_exists(
        $registry,
        $context,
        ExistingFilterField $constraint,
        ConstraintViolationBuilderInterface $violation
    ) {
        $condition = new Condition([
            'field' => 'groups.code',
            'operator' => 'IN',
            'value' => ['tshirts'],
        ]);
        $registry->getFilter('groups.code', 'IN')->willReturn(null);

        $context->buildViolation(
            Argument::any(),
            ['{{ field }}' => 'groups.code', '{{ operator }}' => 'IN']
        )->shouldBeCalled()
        ->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($condition, $constraint);
    }
}
