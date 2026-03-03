<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;

class SchedulingSpec extends ObjectBehavior
{
    function it_is_a_constraint()
    {
        $this->shouldHaveType('Symfony\Component\Validator\Constraint');
    }

    function it_returns_the_name_of_the_class_that_validates_this_constraint()
    {
        $this->validatedBy()->shouldReturn('akeneo_job_instance_scheduling_validator');
    }
}
