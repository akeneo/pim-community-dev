<?php

namespace spec\Pim\Component\User\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\User\Model\GroupInterface;
use Pim\Component\User\Model\Role;
use Pim\Component\User\Updater\GroupUpdater;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GroupUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GroupUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_the_group_properties(GroupInterface $group)
    {
        $group->setName('name')->shouldBeCalled();

        $this->update(
            $group,
            [
                'name' => 'name',
            ]
        );
    }

    function it_throws_an_exception_if_the_given_object_is_not_a_group()
    {
        $this->shouldThrow(InvalidObjectException::class)->during('update', [
            new Role(),
            [
                'name' => 'name',
            ],
        ]);
    }
}
