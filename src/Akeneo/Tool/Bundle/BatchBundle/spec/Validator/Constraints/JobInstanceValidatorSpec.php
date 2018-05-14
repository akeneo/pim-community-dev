<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints;

use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\JobInstance as JobInstanceConstraint;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\UndefinedJobException;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class JobInstanceValidatorSpec extends ObjectBehavior
{
    function let(JobRegistry $jobRegistry, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($jobRegistry);
        $this->initialize($context);
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldHaveType('\Symfony\Component\Validator\ConstraintValidator');
    }

    function it_validates_only_job_instance($context, $object, Constraint $constraint)
    {
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($object, $constraint);
    }

    function it_validates_that_a_job_instance_has_a_known_type(
        $jobRegistry,
        $context,
        Constraint $constraint,
        JobInstance $jobInstance,
        JobInterface $job
    ) {
        $jobInstance->getJobName()->willReturn('my_job_name');
        $jobRegistry->get('my_job_name')->willReturn($job);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($jobInstance, $constraint);
    }

    function it_adds_a_violation_if_job_instance_has_an_unknown_type(
        $jobRegistry,
        $context,
        JobInstanceConstraint $constraint,
        JobInstance $jobInstance,
        ConstraintViolationBuilderInterface $violation
    ) {
        $jobInstance->getJobName()->willReturn(null);
        $jobRegistry->get(null)->willThrow(new UndefinedJobException('The job "" is not registered'));

        $jobInstance->getType()->willReturn('import');

        $context
            ->buildViolation(
                $constraint->message,
                ['%job_type%' => 'import']
            )
            ->shouldBeCalled()
            ->willReturn($violation);

        $violation->atPath($constraint->property)->shouldBeCalled()->willReturn($violation);
        $violation->addViolation()->shouldBeCalled();

        $this->validate($jobInstance, $constraint);
    }
}
