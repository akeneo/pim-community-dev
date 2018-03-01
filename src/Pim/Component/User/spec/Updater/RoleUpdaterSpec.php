<?php

namespace spec\Pim\Component\User\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\User\Model\Group;
use Pim\Component\User\Model\RoleInterface;
use Pim\Component\User\Updater\RoleUpdater;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RoleUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RoleUpdater::class);
    }

    function it_is_a_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_the_role_properties(RoleInterface $role)
    {
        $role->setRole('ROLE_ADMINISTRATOR')->shouldBeCalled();
        $role->setLabel('name')->shouldBeCalled();

        $this->update(
            $role,
            [
                'label' => 'name',
                'role' => 'ROLE_ADMINISTRATOR',
            ]
        );
    }

    function it_throws_an_exception_if_the_given_object_is_not_a_role()
    {
        $this->shouldThrow(InvalidObjectException::class)->during('update', [
            new Group(),
            [
                'name' => 'name',
            ],
        ]);
    }
}
