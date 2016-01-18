<?php

namespace spec\Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Job\JobInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\JobInstance as JobInstanceConstraint;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class JobInstanceValidatorSpec extends ObjectBehavior
{
    function let(ConnectorRegistry $connectorRegistry, ExecutionContextInterface $context)
    {
        $this->beConstructedWith($connectorRegistry);
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
        $connectorRegistry,
        $context,
        Constraint $constraint,
        JobInstance $jobInstance,
        JobInterface $job
    ) {
        $connectorRegistry->getJob($jobInstance)->willReturn($job);
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();

        $this->validate($jobInstance, $constraint);
    }

    function it_adds_a_violation_if_job_instance_has_an_unknown_type(
        $connectorRegistry,
        $context,
        JobInstanceConstraint $constraint,
        JobInstance $jobInstance,
        ConstraintViolationBuilderInterface $violation
    ) {
        $connectorRegistry->getJob($jobInstance)->willReturn(null);
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
