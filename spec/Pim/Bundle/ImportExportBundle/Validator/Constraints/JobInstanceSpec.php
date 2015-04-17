<?php

namespace spec\Pim\Bundle\ImportExportBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;

class JobInstanceSpec extends ObjectBehavior
{
    function it_is_a_constraint()
    {
        $this->shouldHaveType('Symfony\Component\Validator\Constraint');
    }

    function it_has_a_message()
    {
        $this->message->shouldBe('pim_import_export.job_instance.unknown_job_definition');
    }

    function it_has_a_property()
    {
        $this->property->shouldBe('alias');
    }

    function it_returns_the_name_of_the_class_that_validates_this_constraint()
    {
        $this->validatedBy()->shouldReturn('pim_job_instance_validator');
    }

    function it_returns_constraint_targets()
    {
        $this->getTargets()->shouldReturn('class');
    }
}
