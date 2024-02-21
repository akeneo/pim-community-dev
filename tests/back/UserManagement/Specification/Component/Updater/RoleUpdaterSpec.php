<?php

namespace Specification\Akeneo\UserManagement\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Updater\RoleUpdater;
use PhpSpec\ObjectBehavior;

class RoleUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RoleUpdater::class);
    }

    function it_is_an_updater()
    {
        $this->shouldImplement(ObjectUpdaterInterface::class);
    }

    function it_updates_a_role(RoleInterface $role)
    {
        $role->getRole()->willReturn('ROLE_ADMINISTRATOR');
        $role->setRole('ROLE_ADMINISTRATOR')->shouldBeCalled();
        $role->setLabel('admin')->shouldBeCalled();

        $this->update(
            $role,
            [
                'role' => 'ROLE_ADMINISTRATOR',
                'label' => 'admin'
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

    function it_throws_an_exception_if_the_property_is_unknown(RoleInterface $role)
    {
        $this->shouldThrow(UnknownPropertyException::unknownProperty('unknown'))->during(
            'update',
            [$role, ['unknown' => 'anything']]
        );
    }

    function it_throws_an_exception_if_the_data_has_an_invalid_type(RoleInterface $role)
    {
        $this->shouldThrow(InvalidPropertyTypeException::stringExpected('label', RoleUpdater::class, 22))->during(
            'update',
            [$role, ['label' => 22]]
        );
    }
}
