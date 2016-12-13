<?php

namespace spec\Akeneo\ActivityManager\Component\Updater;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Updater\ProjectUpdater;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use Prophecy\Argument;

class ProjectUpdaterSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith($channelRepository, $localeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_nothing_else_than_project($object)
    {
        $this->shouldThrow('\InvalidArgumentException')->during('update', [$object, []]);
    }

    function it_updates_a_project_properties(
        $channelRepository,
        $localeRepository,
        ProjectInterface $project,
        UserInterface $user,
        DatagridView $datagridView,
        ChannelInterface $channel,
        LocaleInterface $locale,
        Group $userGroup
    ) {
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($locale);
        $project->setLabel('Summer collection 2017')->shouldBeCalled();
        $project->setOwner($user)->shouldBeCalled();
        $project->setDueDate(Argument::type(\DateTime::class))->shouldBeCalled();
        $project->setDescription('My description')->shouldBeCalled();
        $project->setDatagridView($datagridView)->shouldBeCalled();
        $project->setChannel($channel)->shouldBeCalled();
        $project->setLocale($locale)->shouldBeCalled();
        $project->addUserGroup($userGroup)->shouldBeCalled();

        $project->getLabel()->willReturn('Summer collection 2017');
        $project->getChannel()->willReturn($channel);
        $project->getLocale()->willReturn($locale);
        $locale->getCode()->willReturn('fr_FR');
        $channel->getCode()->willReturn('print');

        $project->setCode('summer-collection-2017-print-fr-fr')->shouldBeCalled();

        $this->update(
            $project,
            [
                'label' => 'Summer collection 2017',
                'due_date' => '2012-07-16',
                'description' => 'My description',
                'owner' => $user,
                'datagrid_view' => $datagridView,
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'user_groups' => [$userGroup],
            ]
        );
    }
}
