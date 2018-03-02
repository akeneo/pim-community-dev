<?php

namespace spec\Pim\Component\User\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Pim\Component\User\Model\Group;
use Pim\Component\User\Model\RoleInterface;
use Pim\Component\User\Updater\RoleUpdater;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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

    function it_updates_the_role_properties($aclManager, RoleInterface $role)
    {
        $aclManager->getAllExtensions()->willReturn([]);
        $aclManager->getSid(Argument::any())->shouldBeCalled();
        $aclManager->flush()->shouldBeCalled();

        $role->setRole('ROLE_ADMINISTRATOR')->shouldBeCalled();
        $role->getRole()->willreturn('ROLE_ADMINISTRATOR');
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
