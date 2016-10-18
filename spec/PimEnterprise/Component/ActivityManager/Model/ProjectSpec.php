<?php

namespace spec\Akeneo\ActivityManager\Component\Model;

use Akeneo\ActivityManager\Component\Model\Project;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;

class ProjectSpec extends ObjectBehavior
{
    function it_is_initializable()
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

    function it_has_a_description()
    {
        $this->setDescription(null)->shouldReturn(null);
        $this->setDescription('My awesome description')->shouldReturn(null);
        $this->getDescription()->shouldReturn('My awesome description');
    }

    function it_has_a_label()
    {
        $this->setLabel(null)->shouldReturn(null);
        $this->setLabel('My awesome label')->shouldReturn(null);
        $this->getLabel()->shouldReturn('My awesome label');
    }

    function it_has_datagrid_view(DatagridView $datagridView, DatagridView $otherDatagridView)
    {
        $this->addDatagridView($datagridView)->shouldReturn(null);
        $this->addDatagridView($otherDatagridView)->shouldReturn(null);
        $this->removeDatagridView($otherDatagridView)->shouldReturn(null);
        $this->getDatagridViews()->toArray()->shouldReturn([$datagridView]);
    }

    function it_has_a_user_group(Group $group, Group $otherGroup)
    {
        $this->addUserGroup($group)->shouldReturn(null);
        $this->addUserGroup($otherGroup)->shouldReturn(null);
        $this->removeUserGroup($otherGroup)->shouldReturn(null);
        $this->getUserGroups()->toArray()->shouldReturn([$group]);
    }
}
