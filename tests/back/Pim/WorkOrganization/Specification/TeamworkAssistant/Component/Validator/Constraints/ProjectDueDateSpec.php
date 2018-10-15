<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Validator\Constraints\ProjectDueDate;
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
