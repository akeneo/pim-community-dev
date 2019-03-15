<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model;

use Akeneo\UserManagement\Component\Model\Group;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\Project;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;

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
        $this->setDueDate($date)->shouldReturn(null);
        $this->getDueDate()->shouldReturn($date);
    }

    function it_has_an_owner(UserInterface $user)
    {
        $this->setOwner($user)->shouldReturn(null);
        $this->getOwner()->shouldReturn($user);
    }

    function it_has_a_channel(ChannelInterface $channel)
    {
        $this->setChannel($channel)->shouldReturn(null);
        $this->getChannel()->shouldReturn($channel);
    }

    function it_has_a_locale(LocaleInterface $locale)
    {
        $this->setLocale($locale)->shouldReturn(null);
        $this->getLocale()->shouldReturn($locale);
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

    function it_has_datagrid_view(DatagridView $datagridView)
    {
        $this->setDatagridView($datagridView)->shouldReturn(null);
        $this->getDatagridView()->shouldReturn($datagridView);
    }

    function it_has_a_unique_user_group(GroupInterface $group, GroupInterface $otherGroup)
    {
        $this->addUserGroup($group)->shouldReturn(null);
        $this->addUserGroup($otherGroup)->shouldReturn(null);
        $this->removeUserGroup($otherGroup)->shouldReturn(null);
        $this->getUserGroups()->toArray()->shouldReturn([$group]);
    }

    function its_user_groups_is_resettable(GroupInterface $group, GroupInterface $otherGroup)
    {
        $this->addUserGroup($group)->shouldReturn(null);
        $this->addUserGroup($otherGroup)->shouldReturn(null);
        $this->resetUserGroups()->shouldReturn(null);
        $this->getUserGroups()->toArray()->shouldReturn([]);
    }

    function it_has_a_completeness_computing_status()
    {
        $this->isCompletenessComputed()->shouldReturn(false);
        $this->startCompletenessComputing();
        $this->isCompletenessComputed()->shouldReturn(false);
        $this->endCompletenessComputing();
        $this->isCompletenessComputed()->shouldReturn(true);
    }
}
