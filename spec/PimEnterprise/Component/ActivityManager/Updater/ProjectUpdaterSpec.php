<?php

namespace spec\Akeneo\ActivityManager\Component\Updater;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Updater\ProjectUpdater;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use PhpSpec\ObjectBehavior;

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

    function it_updates_a_project(ProjectInterface $project)
    {
        $this->update($project, ['label' => 'Summer collection 2017']);

        $project->setLabel('Summer collection 2017')->shouldBeCalled();
    }
}
