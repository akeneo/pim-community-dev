<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Validator;

use PimEnterprise\Component\TeamworkAssistant\Validator\ProjectIdentifier;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;

class ProjectIdentifierSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectIdentifier::class);
    }

    function it_is_a_constraint()
    {
        $this->shouldHaveType(Constraint::class);
    }

    function it_is_linked_to_a_validator()
    {
        $this->validatedBy()->shouldReturn('project_identifier_validator');
    }
}
