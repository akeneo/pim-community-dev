<?php

namespace spec\Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\UpdatedSinceDate;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\UpdatedSinceNDays;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UpdatedSinceStrategyValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $executionContext)
    {
        $this->initialize($executionContext);
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ImportExportBundle\Validator\Constraints\UpdatedSinceStrategyValidator');
    }
    
    function it_is_validator()
    {
        $this->shouldHaveType('Symfony\Component\Validator\ConstraintValidator');
    }

    function it_does_not_adds_a_violation_if_job_parameter_is_valid(
        $executionContext,
        JobInstance $jobInstance,
        \Pim\Bundle\ImportExportBundle\Validator\Constraints\UpdatedSinceDate $constraint
    ) {
        $jobInstance->getRawParameters()->willReturn([
            'updated_since_strategy' => 'since_date',
        ]);
        $constraint->jobInstance = $jobInstance;

        $executionContext->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate('02/02/2012', $constraint)->shouldReturn(null);
    }

    function it_adds_a_violation_if_updated_since_date_is_empty(
        $executionContext,
        JobInstance $jobInstance,
        UpdatedSinceDate $constraint,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $jobInstance->getRawParameters()->willReturn([
            'updated_since_strategy' => 'since_date',
        ]);

        $constraint->jobInstance = $jobInstance;
        $constraint->strategy = 'since_date';

        $executionContext->buildViolation('pim_connector.export.updated.updated_since_date.error')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate('', $constraint)->shouldReturn(null);
    }
    
    function it_adds_a_violation_if_updated_since_n_days_is_empty(
        $executionContext,
        JobInstance $jobInstance,
        UpdatedSinceNDays $constraint,
        ConstraintViolationBuilderInterface $constraintViolationBuilder
    ) {
        $jobInstance->getRawParameters()->willReturn([
            'updated_since_strategy' => 'since_n_days',
        ]);

        $constraint->jobInstance = $jobInstance;
        $constraint->strategy = 'since_n_days';

        $executionContext->buildViolation('pim_connector.export.updated.updated_since_n_days.error')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->addViolation()->shouldBeCalled();

        $this->validate('', $constraint)->shouldReturn(null);
    }

    function it_thowns_an_exception_if_the_constraint_is_wrong(
        JobInstance $jobInstance,
        Blank $constraint
    ) {
        $this->shouldThrow('Symfony\Component\Validator\Exception\UnexpectedTypeException')
            ->during('validate', [$jobInstance, $constraint]);
    }
}
