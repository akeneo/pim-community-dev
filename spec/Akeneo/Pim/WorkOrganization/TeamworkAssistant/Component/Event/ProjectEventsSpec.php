<?php

namespace spec\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Event;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Event\ProjectEvents;

class ProjectEventsSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(ProjectEvents::class);
    }
}
