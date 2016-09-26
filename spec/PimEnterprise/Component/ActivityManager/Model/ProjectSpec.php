<?php

namespace spec\Akeneo\ActivityManager\Component\Model;

use Akeneo\ActivityManager\Component\Model\Project;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use PhpSpec\ObjectBehavior;

class ProjectSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Project::class);
    }

    function it_a_project()
    {
        $this->shouldImplement(ProjectInterface::class);
    }

    function it_has_a_due_date(\DateTime $date)
    {
        $this->setDueDate(null)->shouldReturn(null);
        $this->setDueDate($date)->shouldReturn(null);
    }
}
