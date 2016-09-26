<?php

namespace spec\Akeneo\ActivityManager\Component\Model;

use Akeneo\ActivityManager\Component\Model\Project;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;

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
        $this->getDueDate()->shouldReturn($date);
    }

    function it_has_datagrid_view(DatagridView $datagridView, DatagridView $otherDatagridView)
    {
        $this->addDatagridView($datagridView)->shouldReturn(null);
        $this->addDatagridView($otherDatagridView)->shouldReturn(null);
        $this->removeDatagridView($otherDatagridView)->shouldReturn(null);
        $this->getDatagridViews()->toArray()->shouldReturn([$datagridView]);
    }
}
