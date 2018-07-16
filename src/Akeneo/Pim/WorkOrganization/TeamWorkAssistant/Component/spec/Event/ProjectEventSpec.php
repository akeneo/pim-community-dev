<?php

namespace spec\Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Event;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Event\ProjectEvent;
use Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Model\ProjectInterface;
use Symfony\Component\EventDispatcher\Event;

class ProjectEventSpec extends ObjectBehavior
{
    function let(ProjectInterface $project)
    {
        $this->beConstructedWith($project);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectEvent::class);
    }

    function it_is_an_event()
    {
        $this->shouldHaveType(Event::class);
    }

    function it_has_a_project($project)
    {
        $this->getProject()->shouldReturn($project);
    }
}
