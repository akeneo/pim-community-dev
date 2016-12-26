<?php

namespace spec\PimEnterprise\Component\ActivityManager\Event;

use PimEnterprise\Component\ActivityManager\Event\ProjectEvents;
use PhpSpec\ObjectBehavior;

class ProjectEventsSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ProjectEvents::class);
    }
}
