<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Event;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\TeamworkAssistant\Event\ProjectEvents;

class ProjectEventsSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ProjectEvents::class);
    }
}
