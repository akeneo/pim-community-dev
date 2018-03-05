<?php

namespace spec\Pim\Component\User\Role;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\User\Group\Group;
use Pim\Component\User\Role\RoleInterface;
use Pim\Component\User\Role\RoleUpdater;

class RoleUpdaterSpec extends ObjectBehavior
{
    function let(AclManager $aclManager)
    {
        $this->beConstructedWith($aclManager);
    }

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
