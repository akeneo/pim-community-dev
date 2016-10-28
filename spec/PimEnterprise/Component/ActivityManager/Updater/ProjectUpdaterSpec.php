<?php

namespace spec\Akeneo\ActivityManager\Component\Updater;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Updater\ProjectUpdater;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;

class ProjectUpdaterSpec extends ObjectBehavior
{
    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
        $this->shouldHaveType(ProjectUpdater::class);
    }

    function it_updates_nothing_else_than_project($object)
    {
        $this->shouldThrow('\InvalidArgumentException')->during('update', [$object, []]);
    }

    function it_updates_a_project(
        ProjectInterface $project,
        UserInterface $user,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $this->update(
            $project,
            [
                'label' => 'Summer collection 2017',
                'owner' => $user,
                'channel' => $channel,
                'locale' => $locale,
            ]
        );

        $project->setLabel('Summer collection 2017')->shouldBeCalled();
        $project->setOwner($user)->shouldBeCalled();
        $project->setChannel($channel)->shouldBeCalled();
        $project->setLocale($locale)->shouldBeCalled();
    }
}
