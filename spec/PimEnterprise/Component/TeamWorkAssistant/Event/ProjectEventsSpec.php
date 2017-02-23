<?php

namespace spec\PimEnterprise\Component\TeamWorkAssistant\Event;

use PimEnterprise\Component\TeamWorkAssistant\Event\ProjectEvents;
use PhpSpec\ObjectBehavior;

class ProjectEventsSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ProjectEvents::class);
    }
}
