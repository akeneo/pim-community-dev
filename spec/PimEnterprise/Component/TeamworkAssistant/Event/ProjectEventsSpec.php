<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Event;

use PimEnterprise\Component\TeamworkAssistant\Event\ProjectEvents;
use PhpSpec\ObjectBehavior;

class ProjectEventsSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ProjectEvents::class);
    }
}
