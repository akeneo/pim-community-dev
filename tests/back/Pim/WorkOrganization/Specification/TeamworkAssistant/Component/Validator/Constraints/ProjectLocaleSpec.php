<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Validator\Constraints\ProjectLocale;
use Symfony\Component\Validator\Constraint;

class ProjectLocaleSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectLocale::class);
    }

    function it_is_a_constraint()
    {
        $this->shouldHaveType(Constraint::class);
    }

    function it_is_linked_to_a_validator()
    {
        $this->validatedBy()->shouldReturn('project_locale_validator');
    }

    function it_validates_the_project_class()
    {
        $this->getTargets()->shouldReturn(ProjectLocale::CLASS_CONSTRAINT);
    }
}
