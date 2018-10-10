<?php

namespace Specification\Akeneo\UserManagement\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Updater\GroupUpdater;
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
