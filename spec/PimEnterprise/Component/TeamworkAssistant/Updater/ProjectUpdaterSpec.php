<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Updater\ProjectUpdater;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
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
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $userRepository
    ) {
        $this->beConstructedWith($channelRepository, $localeRepository, $userRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_nothing_else_than_project()
    {
        $this->shouldThrow(
            InvalidObjectException::objectExpected(
                'stdClass',
                ProjectInterface::class
            )
        )->during('update', [new \stdClass(), []]);
    }

    function it_updates_a_project_properties(
        $userRepository,
        $channelRepository,
        $localeRepository,
        ProjectInterface $project,
        UserInterface $user,
        DatagridView $datagridView,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $userRepository->findOneByIdentifier('julia')->willreturn($user);
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($locale);
        $locale->isActivated()->willReturn(true);

        $project->setLabel('Summer collection 2017')->shouldBeCalled();
        $project->setDueDate(Argument::type(\DateTime::class))->shouldBeCalled();
        $project->setDescription('My description')->shouldBeCalled();
        $project->setOwner($user)->shouldBeCalled();
        $project->setDatagridView($datagridView)->shouldBeCalled();
        $project->setChannel($channel)->shouldBeCalled();
        $project->setLocale($locale)->shouldBeCalled();

        $project->getLabel()->willReturn('Summer collection 2017');
        $project->getChannel()->willReturn($channel);
        $project->getLocale()->willReturn($locale);

        $this->update(
            $project,
            [
                'label' => 'Summer collection 2017',
                'due_date' => '2012-07-16',
                'description' => 'My description',
                'owner' => 'julia',
                'datagrid_view' => $datagridView,
                'channel' => 'ecommerce',
                'locale' => 'fr_FR',
            ]
        );
    }

    function it_throws_exception_if_project_locale_is_disable(
        $localeRepository,
        LocaleInterface $locale,
        ProjectInterface $project
    ) {
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($locale);
        $locale->isActivated()->willReturn(false);

        $this->shouldThrow(InvalidPropertyException::class)->during('update', [
            $project,
            [
                'locale' => 'fr_FR',
            ]
        ]);
    }

    function it_throws_exception_if_the_date_is_invalid(ProjectInterface $project)
    {
        $this->shouldThrow(InvalidPropertyException::class)->during('update', [
            $project,
            [
                'due_date' => '2012-13-02',
            ]
        ]);

        $this->shouldThrow(InvalidPropertyException::class)->during('update', [
            $project,
            [
                'due_date' => 'string',
            ]
        ]);
    }
}
