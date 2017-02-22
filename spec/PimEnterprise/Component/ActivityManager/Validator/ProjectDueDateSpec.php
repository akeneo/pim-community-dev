<?php

namespace spec\PimEnterprise\Component\ActivityManager\Validator;

use PimEnterprise\Component\ActivityManager\Validator\ProjectDueDate;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

class ProjectDueDateSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectDueDate::class);
    }

    function it_is_a_constraint()
    {
        $this->shouldHaveType(Constraint::class);
    }

    function it_is_linked_to_a_validator()
    {
        $this->validatedBy()->shouldReturn('project_due_date_validator');
    }

    function it_validates_the_project_class()
    {
        $this->getTargets()->shouldReturn(ProjectDueDate::CLASS_CONSTRAINT);
    }
}
