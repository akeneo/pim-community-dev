<?php

namespace spec\Akeneo\ActivityManager\Component\Event;

use Akeneo\ActivityManager\Component\Event\ProjectEvents;
use PhpSpec\ObjectBehavior;

class ProjectEventsSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ProjectEvents::class);
    }
}
