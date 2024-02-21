<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;

class JobInstanceSpec extends ObjectBehavior
{
    function it_is_a_constraint()
    {
        $this->shouldHaveType('Symfony\Component\Validator\Constraint');
    }

    function it_has_a_message()
    {
        $this->message->shouldBe('akeneo_batch.job_instance.unknown_job_definition');
    }

    function it_has_a_property()
    {
        $this->property->shouldBe('jobName');
    }

    function it_returns_the_name_of_the_class_that_validates_this_constraint()
    {
        $this->validatedBy()->shouldReturn('akeneo_job_instance_validator');
    }

    function it_returns_constraint_targets()
    {
        $this->getTargets()->shouldReturn('class');
    }
}
