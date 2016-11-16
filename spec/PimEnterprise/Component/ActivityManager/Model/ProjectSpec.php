<?php

namespace spec\Akeneo\ActivityManager\Component\Model;

use Akeneo\ActivityManager\Component\Model\Project;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;

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

    function it_has_a_unique_user_group(Group $group, Group $otherGroup)
    {
        $this->addUserGroup($group)->shouldReturn(null);
        $this->addUserGroup($otherGroup)->shouldReturn(null);
        $this->removeUserGroup($otherGroup)->shouldReturn(null);
        $this->getUserGroups()->toArray()->shouldReturn([$group]);
    }

    function it_has_a_unique_product(ProductInterface $product, ProductInterface $otherProduct)
    {
        $this->addProduct($product)->shouldReturn(null);
        $this->addProduct($otherProduct)->shouldReturn(null);
        $this->getProducts()->toArray()->shouldReturn([$product, $otherProduct]);
    }
}
